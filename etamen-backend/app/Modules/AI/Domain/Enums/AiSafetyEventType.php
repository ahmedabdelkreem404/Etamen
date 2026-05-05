<?php

namespace App\Modules\AI\Domain\Enums;

enum AiSafetyEventType: string
{
    case RedFlagDetected = 'red_flag_detected';
    case RefusalTriggered = 'refusal_triggered';
    case EmergencyGuidance = 'emergency_guidance';
    case MedicationSafety = 'medication_safety';
    case DiagnosisSafety = 'diagnosis_safety';
    case RateLimitHit = 'rate_limit_hit';
    case ProviderError = 'provider_error';
    case ContextBlocked = 'context_blocked';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
