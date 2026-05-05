<?php

namespace App\Modules\Notifications\Application\Services;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\Medications\Domain\Enums\MedicationNotificationStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationNotificationQueue;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;

class NotificationSchedulerService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly NotificationIntegrationService $integrations,
    ) {}

    public function generateAppointmentReminders(): int
    {
        $count = 0;
        $windows = config('notifications.appointment_reminder_windows', [24, 1]);

        foreach ($windows as $window) {
            Appointment::query()
                ->with(['patient', 'slot'])
                ->whereIn('status', [AppointmentStatus::Confirmed->value, AppointmentStatus::Accepted->value])
                ->whereHas('slot', fn ($query) => $query
                    ->where('starts_at', '>=', now())
                    ->where('starts_at', '<=', now()->addHours((int) $window)))
                ->get()
                ->each(function (Appointment $appointment) use ($window, &$count): void {
                    $this->notifications->sendToUser($appointment->patient, 'appointment_reminder', [
                        'appointment_number' => $appointment->appointment_number,
                        'window' => $window,
                    ], [
                        'category' => NotificationCategory::Appointments,
                        'priority' => (int) $window <= 1 ? NotificationPriority::High : NotificationPriority::Normal,
                        'idempotency_key' => 'appointment:'.$appointment->id.':reminder:'.$window,
                        'data' => [
                            'appointment_id' => $appointment->id,
                            'starts_at' => $appointment->slot?->starts_at?->toISOString(),
                        ],
                    ]);
                    $count++;
                });
        }

        return $count;
    }

    public function generateMedicationNotifications(): int
    {
        $count = 0;

        MedicationNotificationQueue::query()
            ->with('patient')
            ->where('status', MedicationNotificationStatus::Pending->value)
            ->where('scheduled_for', '<=', now()->addMinutes(5))
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->each(function (MedicationNotificationQueue $queue) use (&$count): void {
                $this->notifications->sendToUser($queue->patient, 'medication_reminder_due', [
                    'medication_name' => $queue->reminder?->medication_name ?? 'دوائك',
                ], [
                    'category' => NotificationCategory::Medications,
                    'idempotency_key' => 'medication:'.$queue->medication_reminder_id.':'.$queue->scheduled_for?->timestamp,
                    'data' => [
                        'medication_reminder_id' => $queue->medication_reminder_id,
                        'scheduled_for' => $queue->scheduled_for?->toISOString(),
                        'note' => 'Reminder is for organization only.',
                    ],
                ]);

                $queue->forceFill(['status' => MedicationNotificationStatus::Queued->value])->save();
                $count++;
            });

        return $count;
    }

    public function generateCarePlanCheckinReminders(): int
    {
        $count = 0;

        CarePlan::query()
            ->with('patient')
            ->where('status', CarePlanStatus::Active->value)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString()))
            ->get()
            ->each(function (CarePlan $plan) use (&$count): void {
                $this->notifications->sendToUser($plan->patient, 'care_plan_checkin_due', [
                    'plan_title' => $plan->title,
                ], [
                    'category' => NotificationCategory::CarePlans,
                    'idempotency_key' => 'care_plan:'.$plan->id.':checkin:'.now()->toDateString(),
                    'data' => ['care_plan_id' => $plan->id],
                ]);
                $count++;
            });

        return $count;
    }

    public function generateLabResultNotifications(): int
    {
        $count = 0;

        LabResult::query()
            ->with('order.patient')
            ->where('status', LabResultStatus::VisibleToPatient->value)
            ->get()
            ->each(function (LabResult $result) use (&$count): void {
                $this->integrations->notifyLabResultReady($result);
                $count++;
            });

        return $count;
    }

    public function generatePaymentReviewNotifications(): int
    {
        $count = 0;

        Payment::query()
            ->where('status', PaymentStatus::PendingReview->value)
            ->get()
            ->each(function (Payment $payment) use (&$count): void {
                User::role(['admin', 'super_admin'])->get()->each(function (User $admin) use ($payment, &$count): void {
                    $this->notifications->sendToUser($admin, 'payment_pending_review', [
                        'payment_id' => $payment->id,
                    ], [
                        'category' => NotificationCategory::Payments,
                        'priority' => NotificationPriority::High,
                        'idempotency_key' => 'payment:'.$payment->id.':pending_review:admin:'.$admin->id,
                        'data' => ['payment_id' => $payment->id],
                    ]);
                    $count++;
                });
            });

        return $count;
    }

    public function generateAiSafetyAdminAlerts(): int
    {
        $count = 0;

        AiSafetyEvent::query()
            ->whereIn('severity', [AiSafetySeverity::High->value, AiSafetySeverity::Critical->value])
            ->get()
            ->each(function (AiSafetyEvent $event) use (&$count): void {
                $count += $this->integrations->notifyAiSafetyEvent($event);
            });

        return $count;
    }
}
