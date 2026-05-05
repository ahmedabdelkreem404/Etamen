<?php

namespace App\Modules\Notifications\Domain\Enums;

enum NotificationCategory: string
{
    case Appointments = 'appointments';
    case Payments = 'payments';
    case Pharmacy = 'pharmacy';
    case Labs = 'labs';
    case Medications = 'medications';
    case CarePlans = 'care_plans';
    case Wallet = 'wallet';
    case AiSafety = 'ai_safety';
    case System = 'system';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
