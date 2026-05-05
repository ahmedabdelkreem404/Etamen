<?php

namespace App\Modules\AI\Domain\Enums;

enum AiSafetyClassification: string
{
    case Safe = 'safe';
    case MedicalAdviceRequest = 'medical_advice_request';
    case DiagnosisRequest = 'diagnosis_request';
    case MedicationChangeRequest = 'medication_change_request';
    case EmergencyRedFlag = 'emergency_red_flag';
    case MentalHealthCrisis = 'mental_health_crisis';
    case Unsafe = 'unsafe';
    case Unknown = 'unknown';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
