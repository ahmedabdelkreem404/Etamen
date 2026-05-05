<?php

namespace App\Modules\Payments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Application\Services\AppointmentStatusService;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentVerificationService
{
    public function __construct(
        private readonly AppointmentStatusService $appointmentStatusService,
        private readonly InvoiceService $invoiceService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function verifyManualAdmin(Payment $payment, User $actor, string $reason = 'Manual payment verified.', array $metadata = []): Payment
    {
        return $this->verify(
            $payment,
            $actor,
            $reason,
            $metadata + ['verification_source' => 'manual_admin_review'],
            [PaymentStatus::PendingReview],
        );
    }

    public function verifyPaymobCallback(Payment $payment, string $reason = 'Paymob payment verified.', array $metadata = []): Payment
    {
        return $this->verify(
            $payment,
            null,
            $reason,
            $metadata + ['verification_source' => 'paymob_callback'],
            [PaymentStatus::PendingGateway],
        );
    }

    private function verify(Payment $payment, ?User $actor, string $reason, array $metadata, array $allowedStatuses): Payment
    {
        return DB::transaction(function () use ($payment, $actor, $reason, $metadata, $allowedStatuses): Payment {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::Verified) {
                $this->invoiceService->createForPayment($payment);

                return $payment->refresh()->load(['invoice', 'paymentMethod']);
            }

            if (! in_array($payment->status, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'payment' => ['This payment cannot be verified from its current status for this source.'],
                ]);
            }

            $before = $payment->getAttributes();
            $from = $payment->status;

            $payment->forceFill([
                'status' => PaymentStatus::Verified,
                'verified_at' => now(),
                'reviewed_by' => $actor?->id ?? $payment->reviewed_by,
                'metadata' => array_merge($payment->metadata ?? [], $metadata),
            ])->save();

            $payment->statusHistories()->create([
                'from_status' => $from->value,
                'to_status' => PaymentStatus::Verified->value,
                'actor_id' => $actor?->id,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);

            $this->confirmAppointmentIfNeeded($payment, $actor, $metadata);

            $this->invoiceService->createForPayment($payment);

            $this->auditLogService->log('payment.verified', $payment, $actor, before: $before, after: $payment->getAttributes(), metadata: $metadata);

            return $payment->refresh()->load(['invoice', 'paymentMethod']);
        });
    }

    public function fail(Payment $payment, ?User $actor = null, string $reason = 'Payment failed.', array $metadata = []): Payment
    {
        return DB::transaction(function () use ($payment, $actor, $reason, $metadata): Payment {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::Verified) {
                return $payment->refresh();
            }

            if ($payment->status === PaymentStatus::Failed) {
                return $payment->refresh();
            }

            $before = $payment->getAttributes();
            $from = $payment->status;

            $payment->forceFill([
                'status' => PaymentStatus::Failed,
                'metadata' => array_merge($payment->metadata ?? [], $metadata),
            ])->save();

            $payment->statusHistories()->create([
                'from_status' => $from->value,
                'to_status' => PaymentStatus::Failed->value,
                'actor_id' => $actor?->id,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);

            $this->auditLogService->log('payment.failed', $payment, $actor, before: $before, after: $payment->getAttributes(), metadata: $metadata);

            return $payment->refresh();
        });
    }

    private function confirmAppointmentIfNeeded(Payment $payment, ?User $actor, array $metadata): void
    {
        if ($payment->payable_type !== Appointment::class || ! $payment->payable_id) {
            return;
        }

        $appointment = Appointment::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();

        if ($appointment->status === AppointmentStatus::Confirmed) {
            return;
        }

        $this->appointmentStatusService->assertStatus($appointment, [
            AppointmentStatus::PendingPayment,
            AppointmentStatus::PendingPaymentReview,
        ], 'The appointment cannot be confirmed from its current status.');

        $this->appointmentStatusService->transition(
            $appointment,
            AppointmentStatus::Confirmed,
            $actor,
            'appointment.confirmed_after_payment',
            'Payment verified.',
            ['payment_id' => $payment->id] + $metadata,
        );
    }
}
