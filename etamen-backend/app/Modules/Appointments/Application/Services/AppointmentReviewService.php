<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentReview;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use Illuminate\Validation\ValidationException;

class AppointmentReviewService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function create(User $patient, Appointment $appointment, array $data): AppointmentReview
    {
        if ($appointment->status !== AppointmentStatus::Completed) {
            throw ValidationException::withMessages([
                'appointment_id' => ['Only completed appointments can be reviewed.'],
            ]);
        }

        if ($appointment->review()->exists()) {
            throw ValidationException::withMessages([
                'appointment_id' => ['This appointment already has a review.'],
            ]);
        }

        $review = AppointmentReview::query()->create([
            'appointment_id' => $appointment->id,
            'patient_user_id' => $patient->id,
            'doctor_profile_id' => $appointment->doctor_profile_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_visible' => true,
        ]);

        $this->auditLogService->log('appointment_review.created', $review, $patient, metadata: [
            'appointment_id' => $appointment->id,
            'doctor_profile_id' => $appointment->doctor_profile_id,
        ]);

        return $review->load(['appointment', 'doctorProfile']);
    }
}
