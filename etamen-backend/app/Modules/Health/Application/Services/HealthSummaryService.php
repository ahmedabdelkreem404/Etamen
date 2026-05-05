<?php

namespace App\Modules\Health\Application\Services;

use App\Models\User;
use App\Modules\Health\Domain\Enums\VitalFlag;
use App\Modules\Health\Infrastructure\Models\HealthGoal;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use App\Modules\Health\Infrastructure\Models\PatientAllergy;
use App\Modules\Health\Infrastructure\Models\PatientChronicDisease;
use App\Modules\Health\Infrastructure\Models\PatientCurrentMedication;
use App\Modules\Health\Infrastructure\Models\VitalRecord;

class HealthSummaryService
{
    public function __construct(
        private readonly HealthProfileService $profileService,
        private readonly HealthTrendService $trendService,
        private readonly VitalFlagService $flagService,
    ) {}

    public function summary(User $patient): array
    {
        $profile = $this->profileService->profileFor($patient);
        $latest = $this->trendService->latest($patient);
        $latestWeight = $latest->first(fn (VitalRecord $record) => $record->vital_type->value === 'weight');
        $weight = $latestWeight ? (float) $latestWeight->value_decimal : (float) ($profile->weight_kg ?? 0);
        $bmi = $this->flagService->bmi($profile->height_cm ? (float) $profile->height_cm : null, $weight ?: null);
        $warningFlags = [VitalFlag::VeryLow->value, VitalFlag::Low->value, VitalFlag::High->value, VitalFlag::VeryHigh->value];

        return [
            'profile_completion_percentage' => $this->completion($profile),
            'latest_vitals' => $latest,
            'active_chronic_diseases_count' => PatientChronicDisease::query()->where('patient_user_id', $patient->id)->where('is_active', true)->count(),
            'active_allergies_count' => PatientAllergy::query()->where('patient_user_id', $patient->id)->where('is_active', true)->count(),
            'active_current_medications_count' => PatientCurrentMedication::query()->where('patient_user_id', $patient->id)->where('is_active', true)->count(),
            'active_goals_count' => HealthGoal::query()->where('patient_user_id', $patient->id)->where('status', 'active')->count(),
            'non_diagnostic_warning_flags_count' => VitalRecord::query()
                ->where('patient_user_id', $patient->id)
                ->whereIn('flag', $warningFlags)
                ->count(),
            'bmi' => $bmi,
            'safe_disclaimer' => 'المؤشرات المعروضة غير تشخيصية ولا تغني عن مراجعة الطبيب. إذا كانت القراءة متكررة أو مصحوبة بأعراض خطيرة مثل ألم صدر أو ضيق تنفس، تواصل مع الطوارئ فورًا.',
        ];
    }

    private function completion(HealthProfile $profile): int
    {
        $fields = [
            'date_of_birth',
            'gender',
            'height_cm',
            'weight_kg',
            'blood_type',
            'emergency_contact_name',
            'emergency_contact_phone',
        ];

        $filled = collect($fields)->filter(fn (string $field) => filled($profile->{$field}))->count();

        return (int) round(($filled / count($fields)) * 100);
    }
}
