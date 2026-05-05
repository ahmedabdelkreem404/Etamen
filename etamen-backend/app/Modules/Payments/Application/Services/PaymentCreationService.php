<?php

namespace App\Modules\Payments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;

class PaymentCreationService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function createForAppointment(Appointment $appointment, User $actor): Payment
    {
        $payment = Payment::query()->create([
            'payable_type' => Appointment::class,
            'payable_id' => $appointment->id,
            'user_id' => $appointment->patient_user_id,
            'provider_id' => $appointment->provider_id,
            'provider_type' => 'doctor',
            'amount' => $appointment->price,
            'currency' => $appointment->currency,
            'status' => PaymentStatus::AwaitingMethod,
            'created_by' => $actor->id,
            'metadata' => [
                'appointment_number' => $appointment->appointment_number,
                'source' => 'doctor_appointment_booking',
            ],
        ]);

        $payment->statusHistories()->create([
            'from_status' => null,
            'to_status' => PaymentStatus::AwaitingMethod->value,
            'actor_id' => $actor->id,
            'reason' => 'Payment created for paid appointment.',
            'metadata' => ['appointment_id' => $appointment->id],
        ]);

        $appointment->update(['payment_id' => $payment->id]);

        $this->auditLogService->log('payment.created_for_appointment', $payment, $actor, metadata: [
            'appointment_id' => $appointment->id,
        ]);

        return $payment->refresh();
    }
}
