<?php

namespace App\Modules\Payments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Application\Services\AppointmentStatusService;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Radiology\Application\Services\RadiologyOrderService;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualPaymentService
{
    public function __construct(
        private readonly FileStorageService $fileStorageService,
        private readonly PaymentVerificationService $paymentVerificationService,
        private readonly AppointmentStatusService $appointmentStatusService,
        private readonly AuditLogService $auditLogService,
        private readonly RadiologyOrderService $radiologyOrderService,
    ) {}

    public function selectMethod(User $patient, Payment $payment, int $paymentMethodId): array
    {
        return DB::transaction(function () use ($patient, $payment, $paymentMethodId): array {
            $payment = $this->ownPaymentForUpdate($patient, $payment);
            $method = PaymentMethod::query()->whereKey($paymentMethodId)->firstOrFail();

            $this->assertManualMethod($method);

            if (! in_array($payment->status, [PaymentStatus::AwaitingMethod, PaymentStatus::AwaitingProof, PaymentStatus::Rejected], true)) {
                throw ValidationException::withMessages([
                    'payment' => ['This payment cannot select a manual method in its current status.'],
                ]);
            }

            $this->transitionPayment($payment, PaymentStatus::AwaitingProof, $patient, 'Manual payment method selected.', [
                'method_type' => $method->type->value,
            ], ['payment_method_id' => $method->id, 'rejected_at' => null]);
            $this->resetPharmacyOrderForManualRetry($payment, $patient);
            $this->resetLabOrderForManualRetry($payment, $patient);
            $this->resetRadiologyOrderForManualRetry($payment, $patient);

            $this->auditLogService->log('payment.manual_method_selected', $payment, $patient, metadata: [
                'payment_method_id' => $method->id,
                'method_type' => $method->type->value,
            ]);

            return [
                'payment' => $payment->refresh()->load('paymentMethod'),
                'instructions_ar' => $method->instructions_ar,
                'instructions_en' => $method->instructions_en,
            ];
        });
    }

    public function uploadProof(User $patient, Payment $payment, UploadedFile $file, array $data): Payment
    {
        return DB::transaction(function () use ($patient, $payment, $file, $data): Payment {
            $payment = $this->ownPaymentForUpdate($patient, $payment);

            if (! in_array($payment->status, [PaymentStatus::AwaitingProof, PaymentStatus::Rejected], true)) {
                throw ValidationException::withMessages([
                    'payment' => ['This payment cannot receive a proof in its current status.'],
                ]);
            }

            if (! $payment->paymentMethod || ! $this->isManualType($payment->paymentMethod->type)) {
                throw ValidationException::withMessages([
                    'payment_method_id' => ['A manual payment method must be selected before uploading proof.'],
                ]);
            }

            $uploadedFile = $this->fileStorageService->storePrivate(
                $file,
                FileCategory::PaymentProof,
                $patient,
                $payment,
                ['payment_id' => $payment->id],
            );

            $payment->proofs()->create([
                'uploaded_by' => $patient->id,
                'file_id' => $uploadedFile->id,
                'reference_number' => $data['reference_number'] ?? null,
                'sender_phone' => $data['sender_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => PaymentProofStatus::PendingReview,
            ]);

            $this->transitionPayment($payment, PaymentStatus::PendingReview, $patient, 'Manual payment proof uploaded.');
            $this->moveAppointmentToPaymentReview($payment, $patient);
            $this->movePharmacyOrderToPaymentReview($payment, $patient);
            $this->moveLabOrderToPaymentReview($payment, $patient);
            $this->moveRadiologyOrderToPaymentReview($payment, $patient);

            $this->auditLogService->log('payment.manual_proof_uploaded', $payment, $patient, metadata: [
                'file_id' => $uploadedFile->id,
            ]);

            return $payment->refresh()->load(['paymentMethod', 'proofs.file']);
        });
    }

    public function accept(User $admin, Payment $payment): Payment
    {
        return DB::transaction(function () use ($admin, $payment): Payment {
            $payment = Payment::query()->whereKey($payment->id)->with(['proofs', 'paymentMethod'])->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::Verified) {
                return $this->paymentVerificationService->verifyManualAdmin($payment, $admin, 'Manual payment already verified.');
            }

            if ($payment->status !== PaymentStatus::PendingReview) {
                throw ValidationException::withMessages([
                    'payment' => ['Only pending-review manual payments can be accepted.'],
                ]);
            }

            $proof = $payment->proofs()->latest('id')->first();

            if (! $proof) {
                throw ValidationException::withMessages([
                    'proof' => ['This payment has no uploaded proof.'],
                ]);
            }

            $proof->update([
                'status' => PaymentProofStatus::Accepted,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $payment->forceFill(['reviewed_by' => $admin->id])->save();

            $this->auditLogService->log('payment.manual_proof_accepted', $proof, $admin, metadata: [
                'payment_id' => $payment->id,
            ]);

            return $this->paymentVerificationService->verifyManualAdmin($payment, $admin, 'Manual payment proof accepted.', [
                'proof_id' => $proof->id,
            ]);
        });
    }

    public function reject(User $admin, Payment $payment, string $reason): Payment
    {
        return DB::transaction(function () use ($admin, $payment, $reason): Payment {
            $payment = Payment::query()->whereKey($payment->id)->with('proofs')->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::PendingReview) {
                throw ValidationException::withMessages([
                    'payment' => ['Only pending-review manual payments can be rejected.'],
                ]);
            }

            $proof = $payment->proofs()->latest('id')->first();

            if (! $proof) {
                throw ValidationException::withMessages([
                    'proof' => ['This payment has no uploaded proof.'],
                ]);
            }

            $proof->update([
                'status' => PaymentProofStatus::Rejected,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->transitionPayment($payment, PaymentStatus::Rejected, $admin, $reason, [
                'proof_id' => $proof->id,
            ], ['reviewed_by' => $admin->id, 'rejected_at' => now()]);

            $this->returnAppointmentToPendingPayment($payment, $admin, $reason);
            $this->returnPharmacyOrderToPendingPayment($payment, $admin, $reason);
            $this->returnLabOrderToPendingPayment($payment, $admin, $reason);
            $this->returnRadiologyOrderToPendingPayment($payment, $admin, $reason);

            $this->auditLogService->log('payment.manual_proof_rejected', $proof, $admin, metadata: [
                'payment_id' => $payment->id,
                'reason' => $reason,
            ]);

            return $payment->refresh()->load(['paymentMethod', 'proofs.file']);
        });
    }

    private function ownPaymentForUpdate(User $patient, Payment $payment): Payment
    {
        $payment = Payment::query()->whereKey($payment->id)->with('paymentMethod')->lockForUpdate()->firstOrFail();

        if ((int) $payment->user_id !== (int) $patient->id) {
            throw ValidationException::withMessages([
                'payment' => ['You cannot manage this payment.'],
            ]);
        }

        return $payment;
    }

    private function assertManualMethod(PaymentMethod $method): void
    {
        if (! $method->is_active || ! $this->isManualType($method->type)) {
            throw ValidationException::withMessages([
                'payment_method_id' => ['The selected manual payment method is not available.'],
            ]);
        }
    }

    private function isManualType(PaymentMethodType $type): bool
    {
        return in_array($type, [PaymentMethodType::ManualVodafoneCash, PaymentMethodType::ManualInstapay], true);
    }

    private function transitionPayment(Payment $payment, PaymentStatus $to, User $actor, string $reason, array $metadata = [], array $extra = []): void
    {
        $from = $payment->status;

        $payment->forceFill([
            'status' => $to,
            ...$extra,
        ])->save();

        if ($from !== $to) {
            $payment->statusHistories()->create([
                'from_status' => $from->value,
                'to_status' => $to->value,
                'actor_id' => $actor->id,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);
        }
    }

    private function moveAppointmentToPaymentReview(Payment $payment, User $actor): void
    {
        if ($payment->payable_type !== Appointment::class || ! $payment->payable_id) {
            return;
        }

        $appointment = Appointment::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();

        if ($appointment->status === AppointmentStatus::PendingPaymentReview) {
            return;
        }

        $this->appointmentStatusService->assertStatus($appointment, [AppointmentStatus::PendingPayment], 'This appointment cannot move to payment review.');
        $this->appointmentStatusService->transition(
            $appointment,
            AppointmentStatus::PendingPaymentReview,
            $actor,
            'appointment.pending_payment_review',
            'Manual payment proof uploaded.',
            ['payment_id' => $payment->id],
        );
    }

    private function returnAppointmentToPendingPayment(Payment $payment, User $actor, string $reason): void
    {
        if ($payment->payable_type !== Appointment::class || ! $payment->payable_id) {
            return;
        }

        $appointment = Appointment::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();

        if ($appointment->status !== AppointmentStatus::PendingPaymentReview) {
            return;
        }

        $this->appointmentStatusService->transition(
            $appointment,
            AppointmentStatus::PendingPayment,
            $actor,
            'appointment.payment_review_rejected',
            $reason,
            ['payment_id' => $payment->id],
        );
    }

    private function movePharmacyOrderToPaymentReview(Payment $payment, User $actor): void
    {
        if ($payment->payable_type !== PharmacyOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = PharmacyOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();

        if ($order->payment_status === PharmacyOrderPaymentStatus::PendingPaymentReview) {
            return;
        }

        if ($order->payment_status !== PharmacyOrderPaymentStatus::PendingPayment) {
            throw ValidationException::withMessages([
                'payment_status' => ['This pharmacy order cannot move to payment review.'],
            ]);
        }

        $before = $order->getAttributes();
        $order->forceFill([
            'payment_status' => PharmacyOrderPaymentStatus::PendingPaymentReview,
            'order_status' => PharmacyOrderStatus::AwaitingPayment,
        ])->save();

        $order->statusHistories()->create([
            'from_status' => $before['order_status'],
            'to_status' => PharmacyOrderStatus::AwaitingPayment->value,
            'actor_id' => $actor->id,
            'reason' => 'Manual payment proof uploaded.',
            'metadata' => ['payment_id' => $payment->id],
            'created_at' => now(),
        ]);

        $this->auditLogService->log('pharmacy_order.pending_payment_review', $order, $actor, before: $before, after: $order->getAttributes(), metadata: [
            'payment_id' => $payment->id,
        ]);
    }

    private function resetPharmacyOrderForManualRetry(Payment $payment, User $actor): void
    {
        if ($payment->payable_type !== PharmacyOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = PharmacyOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();

        if ($order->payment_status === PharmacyOrderPaymentStatus::PendingPayment) {
            return;
        }

        if (! in_array($order->payment_status, [PharmacyOrderPaymentStatus::Rejected, PharmacyOrderPaymentStatus::Unpaid], true)) {
            return;
        }

        $before = $order->getAttributes();
        $order->forceFill([
            'payment_status' => PharmacyOrderPaymentStatus::PendingPayment,
            'order_status' => PharmacyOrderStatus::AwaitingPayment,
        ])->save();

        $this->auditLogService->log('pharmacy_order.manual_payment_retry_started', $order, $actor, before: $before, after: $order->getAttributes(), metadata: [
            'payment_id' => $payment->id,
        ]);
    }

    private function returnPharmacyOrderToPendingPayment(Payment $payment, User $actor, string $reason): void
    {
        if ($payment->payable_type !== PharmacyOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = PharmacyOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();
        $before = $order->getAttributes();

        $order->forceFill([
            'payment_status' => PharmacyOrderPaymentStatus::Rejected,
            'order_status' => PharmacyOrderStatus::AwaitingPayment,
        ])->save();

        $order->statusHistories()->create([
            'from_status' => $before['order_status'],
            'to_status' => PharmacyOrderStatus::AwaitingPayment->value,
            'actor_id' => $actor->id,
            'reason' => $reason,
            'metadata' => ['payment_id' => $payment->id],
            'created_at' => now(),
        ]);

        $this->auditLogService->log('pharmacy_order.payment_review_rejected', $order, $actor, before: $before, after: $order->getAttributes(), metadata: [
            'payment_id' => $payment->id,
            'reason' => $reason,
        ]);
    }

    private function moveLabOrderToPaymentReview(Payment $payment, User $actor): void
    {
        if ($payment->payable_type !== LabOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = LabOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();

        if ($order->payment_status === LabOrderPaymentStatus::PendingPaymentReview) {
            return;
        }

        if ($order->payment_status !== LabOrderPaymentStatus::PendingPayment) {
            throw ValidationException::withMessages([
                'payment_status' => ['This lab order cannot move to payment review.'],
            ]);
        }

        $before = $order->getAttributes();
        $order->forceFill([
            'payment_status' => LabOrderPaymentStatus::PendingPaymentReview,
            'order_status' => LabOrderStatus::AwaitingPayment,
        ])->save();

        $order->statusHistories()->create([
            'from_status' => $before['order_status'],
            'to_status' => LabOrderStatus::AwaitingPayment->value,
            'actor_id' => $actor->id,
            'reason' => 'Manual payment proof uploaded.',
            'metadata' => ['payment_id' => $payment->id],
            'created_at' => now(),
        ]);

        $this->auditLogService->log('lab_order.pending_payment_review', $order, $actor, before: $before, after: $order->getAttributes(), metadata: [
            'payment_id' => $payment->id,
        ]);
    }

    private function resetLabOrderForManualRetry(Payment $payment, User $actor): void
    {
        if ($payment->payable_type !== LabOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = LabOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();

        if ($order->payment_status === LabOrderPaymentStatus::PendingPayment) {
            return;
        }

        if (! in_array($order->payment_status, [LabOrderPaymentStatus::Failed, LabOrderPaymentStatus::Unpaid], true)) {
            return;
        }

        $before = $order->getAttributes();
        $order->forceFill([
            'payment_status' => LabOrderPaymentStatus::PendingPayment,
            'order_status' => LabOrderStatus::AwaitingPayment,
        ])->save();

        $this->auditLogService->log('lab_order.manual_payment_retry_started', $order, $actor, before: $before, after: $order->getAttributes(), metadata: [
            'payment_id' => $payment->id,
        ]);
    }

    private function returnLabOrderToPendingPayment(Payment $payment, User $actor, string $reason): void
    {
        if ($payment->payable_type !== LabOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = LabOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();
        $before = $order->getAttributes();

        $order->forceFill([
            'payment_status' => LabOrderPaymentStatus::Failed,
            'order_status' => LabOrderStatus::AwaitingPayment,
        ])->save();

        $order->statusHistories()->create([
            'from_status' => $before['order_status'],
            'to_status' => LabOrderStatus::AwaitingPayment->value,
            'actor_id' => $actor->id,
            'reason' => $reason,
            'metadata' => ['payment_id' => $payment->id],
            'created_at' => now(),
        ]);

        $this->auditLogService->log('lab_order.payment_review_rejected', $order, $actor, before: $before, after: $order->getAttributes(), metadata: [
            'payment_id' => $payment->id,
            'reason' => $reason,
        ]);
    }

    private function moveRadiologyOrderToPaymentReview(Payment $payment, User $actor): void
    {
        if ($payment->payable_type !== RadiologyOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = RadiologyOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();
        $this->radiologyOrderService->moveToPaymentReview($order, $actor, ['payment_id' => $payment->id]);
    }

    private function resetRadiologyOrderForManualRetry(Payment $payment, User $actor): void
    {
        if ($payment->payable_type !== RadiologyOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = RadiologyOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();
        $this->radiologyOrderService->returnToPendingPayment($order, $actor, 'Manual payment retry started.');
    }

    private function returnRadiologyOrderToPendingPayment(Payment $payment, User $actor, string $reason): void
    {
        if ($payment->payable_type !== RadiologyOrder::class || ! $payment->payable_id) {
            return;
        }

        $order = RadiologyOrder::query()->whereKey($payment->payable_id)->lockForUpdate()->firstOrFail();
        $this->radiologyOrderService->returnToPendingPayment($order, $actor, $reason);
    }
}
