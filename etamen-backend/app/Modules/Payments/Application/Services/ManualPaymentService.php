<?php

namespace App\Modules\Payments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Application\Services\AppointmentStatusService;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
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
                return $this->paymentVerificationService->verify($payment, $admin, 'Manual payment already verified.');
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

            return $this->paymentVerificationService->verify($payment, $admin, 'Manual payment proof accepted.', [
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
}
