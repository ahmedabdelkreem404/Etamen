<?php

namespace App\Modules\AI\Application\Services;

use App\Models\User;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use App\Modules\Health\Infrastructure\Models\PatientAllergy;
use App\Modules\Health\Infrastructure\Models\PatientChronicDisease;
use App\Modules\Health\Infrastructure\Models\PatientCurrentMedication;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use Carbon\CarbonImmutable;

class AiContextBuilderService
{
    public function buildForConversation(AiConversation $conversation): ?array
    {
        if (! $conversation->context_enabled) {
            return null;
        }

        return $this->preview($conversation->patient);
    }

    public function preview(User $patient): array
    {
        $profile = HealthProfile::query()->where('patient_user_id', $patient->id)->first();

        return [
            'notice' => 'Context is user-provided and may be incomplete. Do not diagnose.',
            'profile' => [
                'age' => $profile?->date_of_birth ? CarbonImmutable::parse($profile->date_of_birth)->age : null,
                'gender' => $profile?->gender?->value,
            ],
            'latest_vitals' => $this->latestVitals($patient),
            'active_chronic_diseases' => PatientChronicDisease::query()
                ->where('patient_user_id', $patient->id)
                ->where('is_active', true)
                ->limit(20)
                ->pluck('name')
                ->values()
                ->all(),
            'active_allergies' => PatientAllergy::query()
                ->where('patient_user_id', $patient->id)
                ->where('is_active', true)
                ->limit(20)
                ->pluck('allergen')
                ->values()
                ->all(),
            'current_medications' => PatientCurrentMedication::query()
                ->where('patient_user_id', $patient->id)
                ->where('is_active', true)
                ->limit(20)
                ->pluck('medication_name')
                ->values()
                ->all(),
            'medication_adherence_summary' => $this->medicationAdherence($patient),
            'active_care_plans' => $this->activeCarePlans($patient),
            'recent_appointments' => $this->recentAppointments($patient),
        ];
    }

    private function latestVitals(User $patient): array
    {
        return VitalRecord::query()
            ->where('patient_user_id', $patient->id)
            ->latest('measured_at')
            ->get()
            ->unique(fn (VitalRecord $record) => $record->vital_type->value)
            ->take(10)
            ->map(fn (VitalRecord $record): array => [
                'type' => $record->vital_type->value,
                'value' => $record->value_decimal !== null ? (float) $record->value_decimal : null,
                'secondary_value' => $record->value_secondary_decimal !== null ? (float) $record->value_secondary_decimal : null,
                'unit' => $record->unit,
                'flag' => $record->flag?->value,
                'measured_at' => $record->measured_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    private function medicationAdherence(User $patient): array
    {
        $counts = MedicationLog::query()
            ->where('patient_user_id', $patient->id)
            ->where('scheduled_for', '>=', now()->subDays(30))
            ->selectRaw('action, count(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->all();

        return [
            'range_days' => 30,
            'taken' => (int) ($counts['taken'] ?? 0),
            'skipped' => (int) ($counts['skipped'] ?? 0),
            'missed' => (int) ($counts['missed'] ?? 0),
            'note' => 'Adherence summary is organizational only and is not medication advice.',
        ];
    }

    private function activeCarePlans(User $patient): array
    {
        return CarePlan::query()
            ->where('patient_user_id', $patient->id)
            ->where('status', 'active')
            ->withCount(['checkins', 'mealLogs'])
            ->limit(10)
            ->get()
            ->map(fn (CarePlan $plan): array => [
                'title' => $plan->title,
                'plan_type' => $plan->plan_type->value,
                'status' => $plan->status->value,
                'checkins_count' => $plan->checkins_count,
                'meal_logs_count' => $plan->meal_logs_count,
            ])
            ->values()
            ->all();
    }

    private function recentAppointments(User $patient): array
    {
        return Appointment::query()
            ->where('patient_user_id', $patient->id)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Appointment $appointment): array => [
                'provider_id' => $appointment->provider_id,
                'status' => $appointment->status->value,
                'consultation_type' => $appointment->consultation_type->value,
                'booked_at' => $appointment->booked_at?->toISOString(),
            ])
            ->values()
            ->all();
    }
}
