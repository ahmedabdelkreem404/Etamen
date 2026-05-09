<?php

namespace App\Modules\Payments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;

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

    public function createForPharmacyOrder(PharmacyOrder $order, User $actor): Payment
    {
        $payment = Payment::query()->create([
            'payable_type' => PharmacyOrder::class,
            'payable_id' => $order->id,
            'user_id' => $order->patient_user_id,
            'provider_id' => $order->pharmacy_provider_id,
            'provider_type' => 'pharmacy',
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'status' => PaymentStatus::AwaitingMethod,
            'created_by' => $actor->id,
            'metadata' => [
                'order_number' => $order->order_number,
                'source' => 'pharmacy_order',
            ],
        ]);

        $payment->statusHistories()->create([
            'from_status' => null,
            'to_status' => PaymentStatus::AwaitingMethod->value,
            'actor_id' => $actor->id,
            'reason' => 'Payment created for pharmacy order.',
            'metadata' => ['pharmacy_order_id' => $order->id],
        ]);

        $order->update(['payment_id' => $payment->id]);

        $this->auditLogService->log('payment.created_for_pharmacy_order', $payment, $actor, metadata: [
            'pharmacy_order_id' => $order->id,
        ]);

        return $payment->refresh();
    }

    public function createForLabOrder(LabOrder $order, User $actor): Payment
    {
        $payment = Payment::query()->create([
            'payable_type' => LabOrder::class,
            'payable_id' => $order->id,
            'user_id' => $order->patient_user_id,
            'provider_id' => $order->lab_provider_id,
            'provider_type' => 'lab',
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'status' => PaymentStatus::AwaitingMethod,
            'created_by' => $actor->id,
            'metadata' => [
                'order_number' => $order->order_number,
                'source' => 'lab_order',
            ],
        ]);

        $payment->statusHistories()->create([
            'from_status' => null,
            'to_status' => PaymentStatus::AwaitingMethod->value,
            'actor_id' => $actor->id,
            'reason' => 'Payment created for lab order.',
            'metadata' => ['lab_order_id' => $order->id],
        ]);

        $order->update(['payment_id' => $payment->id]);

        $this->auditLogService->log('payment.created_for_lab_order', $payment, $actor, metadata: [
            'lab_order_id' => $order->id,
        ]);

        return $payment->refresh();
    }

    public function createForRadiologyOrder(RadiologyOrder $order, User $actor): Payment
    {
        $payment = Payment::query()->create([
            'payable_type' => RadiologyOrder::class,
            'payable_id' => $order->id,
            'user_id' => $order->patient_user_id,
            'provider_id' => $order->provider_id,
            'provider_type' => 'radiology',
            'amount' => $order->total_amount,
            'currency' => 'EGP',
            'status' => PaymentStatus::AwaitingMethod,
            'created_by' => $actor->id,
            'metadata' => [
                'order_number' => $order->order_number,
                'source' => 'radiology_order',
            ],
        ]);

        $payment->statusHistories()->create([
            'from_status' => null,
            'to_status' => PaymentStatus::AwaitingMethod->value,
            'actor_id' => $actor->id,
            'reason' => 'Payment created for radiology order.',
            'metadata' => ['radiology_order_id' => $order->id],
        ]);

        $order->update(['payment_id' => $payment->id]);

        $this->auditLogService->log('payment.created_for_radiology_order', $payment, $actor, metadata: [
            'radiology_order_id' => $order->id,
        ]);

        return $payment->refresh();
    }

    public function createForGymBooking(GymBooking $booking, User $actor): Payment
    {
        $payment = Payment::query()->create([
            'payable_type' => GymBooking::class,
            'payable_id' => $booking->id,
            'user_id' => $booking->patient_user_id,
            'provider_id' => $booking->provider_id,
            'provider_type' => 'gym',
            'amount' => $booking->total_amount,
            'currency' => 'EGP',
            'status' => PaymentStatus::AwaitingMethod,
            'created_by' => $actor->id,
            'metadata' => [
                'booking_number' => $booking->booking_number,
                'source' => 'gym_booking',
            ],
        ]);

        $payment->statusHistories()->create([
            'from_status' => null,
            'to_status' => PaymentStatus::AwaitingMethod->value,
            'actor_id' => $actor->id,
            'reason' => 'Payment created for gym booking.',
            'metadata' => ['gym_booking_id' => $booking->id],
        ]);

        $booking->update(['payment_id' => $payment->id]);

        $this->auditLogService->log('payment.created_for_gym_booking', $payment, $actor, metadata: [
            'gym_booking_id' => $booking->id,
        ]);

        return $payment->refresh();
    }

    public function createForCoachBooking(CoachBooking $booking, User $actor): Payment
    {
        $providerType = $booking->coachProvider?->type?->value ?? 'fitness_coach';

        $payment = Payment::query()->create([
            'payable_type' => CoachBooking::class,
            'payable_id' => $booking->id,
            'user_id' => $booking->patient_user_id,
            'provider_id' => $booking->coach_provider_id,
            'provider_type' => $providerType,
            'amount' => $booking->total_amount,
            'currency' => 'EGP',
            'status' => PaymentStatus::AwaitingMethod,
            'created_by' => $actor->id,
            'metadata' => [
                'booking_number' => $booking->booking_number,
                'source' => 'coach_booking',
            ],
        ]);

        $payment->statusHistories()->create([
            'from_status' => null,
            'to_status' => PaymentStatus::AwaitingMethod->value,
            'actor_id' => $actor->id,
            'reason' => 'Payment created for coach booking.',
            'metadata' => ['coach_booking_id' => $booking->id],
        ]);

        $booking->update(['payment_id' => $payment->id]);

        $this->auditLogService->log('payment.created_for_coach_booking', $payment, $actor, metadata: [
            'coach_booking_id' => $booking->id,
        ]);

        return $payment->refresh();
    }
}
