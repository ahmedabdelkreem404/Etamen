<?php

namespace App\Modules\Fitness\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Fitness\Domain\Enums\GymBookingStatus;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Application\Services\PaymentCreationService;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GymBookingService
{
    public function __construct(
        private readonly GymBookingNumberGenerator $numberGenerator,
        private readonly GymBookingStatusService $statusService,
        private readonly PaymentCreationService $paymentCreationService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $patient, array $data): GymBooking
    {
        if (! $patient->hasRole(UserRole::Patient->value)) {
            throw new AuthorizationException('Only patients can create gym bookings.');
        }

        return DB::transaction(function () use ($patient, $data): GymBooking {
            $provider = Provider::query()
                ->whereKey($data['provider_id'])
                ->where('type', ProviderType::Gym)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertPublicGymProvider($provider);
            $branch = $this->resolveBranch($provider, $data['branch_id'] ?? null);
            [$plan, $class] = $this->resolveBookable($provider, $branch, $data);
            $price = $plan ? (float) $plan->price : (float) ($class?->price ?? 0);
            $total = max(round($price, 2), 0);
            $status = $total > 0 ? GymBookingStatus::PendingPayment : GymBookingStatus::Confirmed;

            $booking = GymBooking::query()->create([
                'booking_number' => $this->numberGenerator->generate(),
                'patient_user_id' => $patient->id,
                'provider_id' => $provider->id,
                'branch_id' => $branch?->id ?? $plan?->branch_id ?? $class?->branch_id,
                'membership_plan_id' => $plan?->id,
                'gym_class_id' => $class?->id,
                'status' => $status,
                'total_amount' => $total,
                'starts_at' => $class?->starts_at ?? now(),
                'ends_at' => $class?->ends_at ?? ($plan ? now()->addDays($plan->duration_days) : null),
                'notes' => $data['notes'] ?? null,
            ]);

            $booking->statusHistories()->create([
                'from_status' => null,
                'to_status' => $status,
                'changed_by' => $patient->id,
                'reason' => 'Gym booking created.',
                'metadata' => [
                    'membership_plan_id' => $plan?->id,
                    'gym_class_id' => $class?->id,
                ],
            ]);

            if ($total > 0) {
                $payment = $this->paymentCreationService->createForGymBooking($booking, $patient);
                $booking->forceFill(['payment_id' => $payment->id])->save();
            }

            $this->auditLogService->log('gym_booking.created', $booking, $patient, metadata: [
                'provider_id' => $provider->id,
                'total_amount' => $total,
            ]);

            return $booking->refresh()->load(['provider', 'branch', 'membershipPlan', 'gymClass', 'payment.paymentMethod']);
        });
    }

    public function cancelByPatient(User $patient, GymBooking $booking, ?string $reason = null): GymBooking
    {
        if ((int) $booking->patient_user_id !== (int) $patient->id) {
            throw new AuthorizationException('You cannot cancel this gym booking.');
        }

        return DB::transaction(function () use ($patient, $booking, $reason): GymBooking {
            $booking = GymBooking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (! in_array($booking->status, [GymBookingStatus::PendingPayment, GymBookingStatus::PendingPaymentReview], true)) {
                throw ValidationException::withMessages([
                    'status' => ['This gym booking cannot be cancelled from its current status.'],
                ]);
            }

            return $this->statusService->transition(
                $booking,
                GymBookingStatus::CancelledByUser,
                $patient,
                'gym_booking.cancelled_by_user',
                $reason,
                [],
                ['cancelled_at' => now()],
            )->load(['provider', 'branch', 'membershipPlan', 'gymClass', 'payment.paymentMethod']);
        });
    }

    public function moveToPaymentReview(GymBooking $booking, User $actor, array $metadata = []): GymBooking
    {
        if ($booking->status === GymBookingStatus::PendingPaymentReview) {
            return $booking->refresh();
        }

        if ($booking->status !== GymBookingStatus::PendingPayment) {
            throw ValidationException::withMessages([
                'status' => ['This gym booking cannot move to payment review.'],
            ]);
        }

        return $this->statusService->transition(
            $booking,
            GymBookingStatus::PendingPaymentReview,
            $actor,
            'gym_booking.pending_payment_review',
            'Manual payment proof uploaded.',
            $metadata,
        );
    }

    public function returnToPendingPayment(GymBooking $booking, User $actor, string $reason): GymBooking
    {
        if ($booking->status !== GymBookingStatus::PendingPaymentReview) {
            return $booking->refresh();
        }

        return $this->statusService->transition(
            $booking,
            GymBookingStatus::PendingPayment,
            $actor,
            'gym_booking.payment_review_rejected',
            $reason,
        );
    }

    public function markPaidAfterPayment(GymBooking $booking, ?User $actor, array $metadata = []): GymBooking
    {
        return DB::transaction(function () use ($booking, $actor, $metadata): GymBooking {
            $booking = GymBooking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (in_array($booking->status, [GymBookingStatus::Paid, GymBookingStatus::Confirmed], true)) {
                return $booking->refresh();
            }

            if (! in_array($booking->status, [GymBookingStatus::PendingPayment, GymBookingStatus::PendingPaymentReview], true)) {
                throw ValidationException::withMessages([
                    'status' => ['This gym booking cannot be marked paid from its current status.'],
                ]);
            }

            return $this->statusService->transition(
                $booking,
                GymBookingStatus::Confirmed,
                $actor,
                'gym_booking.confirmed_after_payment',
                'Payment verified.',
                $metadata,
            );
        });
    }

    private function assertPublicGymProvider(Provider $provider): void
    {
        if (
            $provider->type !== ProviderType::Gym
            || $provider->status !== ProviderStatus::Approved
            || ! $provider->is_active
        ) {
            throw ValidationException::withMessages([
                'provider_id' => ['The selected gym provider is not available.'],
            ]);
        }
    }

    private function resolveBranch(Provider $provider, ?int $branchId): ?ProviderBranch
    {
        if (! $branchId) {
            return null;
        }

        $branch = ProviderBranch::query()
            ->whereKey($branchId)
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->first();

        if (! $branch) {
            throw ValidationException::withMessages([
                'branch_id' => ['The selected branch does not belong to this gym provider.'],
            ]);
        }

        return $branch;
    }

    private function resolveBookable(Provider $provider, ?ProviderBranch $branch, array $data): array
    {
        $planId = $data['membership_plan_id'] ?? null;
        $classId = $data['gym_class_id'] ?? null;

        if (($planId && $classId) || (! $planId && ! $classId)) {
            throw ValidationException::withMessages([
                'booking' => ['Choose either a membership plan or a class.'],
            ]);
        }

        if ($planId) {
            $plan = GymMembershipPlan::query()
                ->whereKey($planId)
                ->where('provider_id', $provider->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            if ($branch && $plan->branch_id && (int) $plan->branch_id !== (int) $branch->id) {
                throw ValidationException::withMessages([
                    'branch_id' => ['This membership plan is not available at the selected branch.'],
                ]);
            }

            return [$plan, null];
        }

        $class = GymClassModel::query()
            ->whereKey($classId)
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->lockForUpdate()
            ->firstOrFail();

        if ($branch && $class->branch_id && (int) $class->branch_id !== (int) $branch->id) {
            throw ValidationException::withMessages([
                'branch_id' => ['This class is not available at the selected branch.'],
            ]);
        }

        return [null, $class];
    }
}
