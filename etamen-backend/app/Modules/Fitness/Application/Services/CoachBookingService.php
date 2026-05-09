<?php

namespace App\Modules\Fitness\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Fitness\Domain\Enums\CoachAvailabilityStatus;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Application\Services\PaymentCreationService;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoachBookingService
{
    public function __construct(
        private readonly CoachBookingNumberGenerator $numberGenerator,
        private readonly CoachBookingStatusService $statusService,
        private readonly PaymentCreationService $paymentCreationService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $patient, array $data): CoachBooking
    {
        if (! $patient->hasRole(UserRole::Patient->value)) {
            throw new AuthorizationException('Only patients can create coach bookings.');
        }

        return DB::transaction(function () use ($patient, $data): CoachBooking {
            $provider = Provider::query()
                ->whereKey($data['coach_provider_id'])
                ->whereIn('type', [ProviderType::FitnessCoach, ProviderType::NutritionCoach])
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertPublicCoachProvider($provider);
            $sessionType = $this->resolveSessionType($provider, $data['session_type_id']);
            $slot = $this->resolveSlot($provider, $data['availability_slot_id'] ?? null);
            $total = max(round((float) $sessionType->price, 2), 0);
            $status = $total > 0 ? CoachBookingStatus::PendingPayment : CoachBookingStatus::Confirmed;

            $booking = CoachBooking::query()->create([
                'booking_number' => $this->numberGenerator->generate(),
                'patient_user_id' => $patient->id,
                'coach_provider_id' => $provider->id,
                'session_type_id' => $sessionType->id,
                'availability_slot_id' => $slot?->id,
                'status' => $status,
                'total_amount' => $total,
                'patient_goal' => $data['patient_goal'] ?? null,
            ]);

            if ($slot) {
                $slot->forceFill(['status' => CoachAvailabilityStatus::Booked])->save();
            }

            $booking->statusHistories()->create([
                'from_status' => null,
                'to_status' => $status,
                'changed_by' => $patient->id,
                'reason' => 'Coach booking created.',
                'metadata' => [
                    'session_type_id' => $sessionType->id,
                    'availability_slot_id' => $slot?->id,
                ],
            ]);

            if ($total > 0) {
                $payment = $this->paymentCreationService->createForCoachBooking($booking, $patient);
                $booking->forceFill(['payment_id' => $payment->id])->save();
            }

            $this->auditLogService->log('coach_booking.created', $booking, $patient, metadata: [
                'coach_provider_id' => $provider->id,
                'total_amount' => $total,
            ]);

            return $booking->refresh()->load(['coachProvider', 'sessionType', 'availabilitySlot', 'payment.paymentMethod']);
        });
    }

    public function cancelByPatient(User $patient, CoachBooking $booking, ?string $reason = null): CoachBooking
    {
        if ((int) $booking->patient_user_id !== (int) $patient->id) {
            throw new AuthorizationException('You cannot cancel this coach booking.');
        }

        return DB::transaction(function () use ($patient, $booking, $reason): CoachBooking {
            $booking = CoachBooking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (! in_array($booking->status, [CoachBookingStatus::PendingPayment, CoachBookingStatus::PendingPaymentReview], true)) {
                throw ValidationException::withMessages([
                    'status' => ['This coach booking cannot be cancelled from its current status.'],
                ]);
            }

            if ($booking->availability_slot_id) {
                CoachAvailabilitySlot::query()
                    ->whereKey($booking->availability_slot_id)
                    ->where('status', CoachAvailabilityStatus::Booked)
                    ->update(['status' => CoachAvailabilityStatus::Available]);
            }

            return $this->statusService->transition(
                $booking,
                CoachBookingStatus::CancelledByUser,
                $patient,
                'coach_booking.cancelled_by_user',
                $reason,
                [],
                ['cancelled_at' => now()],
            )->load(['coachProvider', 'sessionType', 'availabilitySlot', 'payment.paymentMethod']);
        });
    }

    public function moveToPaymentReview(CoachBooking $booking, User $actor, array $metadata = []): CoachBooking
    {
        if ($booking->status === CoachBookingStatus::PendingPaymentReview) {
            return $booking->refresh();
        }

        if ($booking->status !== CoachBookingStatus::PendingPayment) {
            throw ValidationException::withMessages([
                'status' => ['This coach booking cannot move to payment review.'],
            ]);
        }

        return $this->statusService->transition(
            $booking,
            CoachBookingStatus::PendingPaymentReview,
            $actor,
            'coach_booking.pending_payment_review',
            'Manual payment proof uploaded.',
            $metadata,
        );
    }

    public function returnToPendingPayment(CoachBooking $booking, User $actor, string $reason): CoachBooking
    {
        if ($booking->status !== CoachBookingStatus::PendingPaymentReview) {
            return $booking->refresh();
        }

        return $this->statusService->transition(
            $booking,
            CoachBookingStatus::PendingPayment,
            $actor,
            'coach_booking.payment_review_rejected',
            $reason,
        );
    }

    public function markPaidAfterPayment(CoachBooking $booking, ?User $actor, array $metadata = []): CoachBooking
    {
        return DB::transaction(function () use ($booking, $actor, $metadata): CoachBooking {
            $booking = CoachBooking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (in_array($booking->status, [CoachBookingStatus::Paid, CoachBookingStatus::Confirmed], true)) {
                return $booking->refresh();
            }

            if (! in_array($booking->status, [CoachBookingStatus::PendingPayment, CoachBookingStatus::PendingPaymentReview], true)) {
                throw ValidationException::withMessages([
                    'status' => ['This coach booking cannot be marked paid from its current status.'],
                ]);
            }

            return $this->statusService->transition(
                $booking,
                CoachBookingStatus::Confirmed,
                $actor,
                'coach_booking.confirmed_after_payment',
                'Payment verified.',
                $metadata,
            );
        });
    }

    private function assertPublicCoachProvider(Provider $provider): void
    {
        if (
            ! in_array($provider->type, [ProviderType::FitnessCoach, ProviderType::NutritionCoach], true)
            || $provider->status !== ProviderStatus::Approved
            || ! $provider->is_active
        ) {
            throw ValidationException::withMessages([
                'coach_provider_id' => ['The selected coach is not available.'],
            ]);
        }
    }

    private function resolveSessionType(Provider $provider, int $sessionTypeId): CoachSessionType
    {
        return CoachSessionType::query()
            ->whereKey($sessionTypeId)
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function resolveSlot(Provider $provider, ?int $slotId): ?CoachAvailabilitySlot
    {
        if (! $slotId) {
            return null;
        }

        $slot = CoachAvailabilitySlot::query()
            ->whereKey($slotId)
            ->where('provider_id', $provider->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($slot->status !== CoachAvailabilityStatus::Available) {
            throw ValidationException::withMessages([
                'availability_slot_id' => ['The selected coach slot is not available.'],
            ]);
        }

        return $slot;
    }
}
