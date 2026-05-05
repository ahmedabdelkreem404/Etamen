<?php

namespace App\Modules\Health\Application\Services;

use App\Modules\Health\Domain\Enums\BmiCategory;
use App\Modules\Health\Domain\Enums\VitalFlag;
use App\Modules\Health\Domain\Enums\VitalType;

class VitalFlagService
{
    public function flag(VitalType $type, ?float $value, ?float $secondary = null, array $metadata = []): VitalFlag
    {
        return match ($type) {
            VitalType::BloodPressure => $this->bloodPressure($value, $secondary),
            VitalType::BloodSugar => $this->bloodSugar($value, (string) ($metadata['context'] ?? 'unknown')),
            VitalType::HeartRate => $this->heartRate($value),
            VitalType::OxygenSaturation => $this->oxygen($value),
            VitalType::Temperature => $this->temperature($value),
            default => VitalFlag::Unknown,
        };
    }

    public function unitFor(VitalType $type): ?string
    {
        return match ($type) {
            VitalType::BloodPressure => 'mmHg',
            VitalType::BloodSugar => 'mg/dL',
            VitalType::HeartRate => 'bpm',
            VitalType::OxygenSaturation => '%',
            VitalType::Temperature => 'C',
            VitalType::Weight => 'kg',
            VitalType::Sleep => 'hours',
            default => null,
        };
    }

    public function bmi(?float $heightCm, ?float $weightKg): array
    {
        if (! $heightCm || ! $weightKg || $heightCm <= 0 || $weightKg <= 0) {
            return ['value' => null, 'category' => BmiCategory::Unknown->value];
        }

        $meters = $heightCm / 100;
        $bmi = round($weightKg / ($meters * $meters), 2);

        $category = match (true) {
            $bmi < 18.5 => BmiCategory::Underweight,
            $bmi < 25 => BmiCategory::Normal,
            $bmi < 30 => BmiCategory::Overweight,
            default => BmiCategory::Obese,
        };

        return ['value' => $bmi, 'category' => $category->value];
    }

    private function bloodPressure(?float $systolic, ?float $diastolic): VitalFlag
    {
        if ($systolic === null || $diastolic === null) {
            return VitalFlag::Unknown;
        }

        return match (true) {
            $systolic < 90 || $diastolic < 60 => VitalFlag::Low,
            $systolic >= 180 || $diastolic >= 120 => VitalFlag::VeryHigh,
            $systolic >= 140 || $diastolic >= 90 => VitalFlag::High,
            default => VitalFlag::Normal,
        };
    }

    private function bloodSugar(?float $value, string $context): VitalFlag
    {
        if ($value === null || $context === 'unknown') {
            return VitalFlag::Unknown;
        }

        if ($value < 70) {
            return VitalFlag::Low;
        }

        if (in_array($context, ['fasting', 'before_sleep'], true)) {
            return match (true) {
                $value >= 250 => VitalFlag::VeryHigh,
                $value >= 126 => VitalFlag::High,
                default => VitalFlag::Normal,
            };
        }

        return match (true) {
            $value >= 300 => VitalFlag::VeryHigh,
            $value >= 200 => VitalFlag::High,
            default => VitalFlag::Normal,
        };
    }

    private function heartRate(?float $value): VitalFlag
    {
        if ($value === null) {
            return VitalFlag::Unknown;
        }

        return match (true) {
            $value < 50 => VitalFlag::Low,
            $value > 120 => VitalFlag::High,
            default => VitalFlag::Normal,
        };
    }

    private function oxygen(?float $value): VitalFlag
    {
        if ($value === null) {
            return VitalFlag::Unknown;
        }

        return match (true) {
            $value < 90 => VitalFlag::VeryLow,
            $value <= 93 => VitalFlag::Low,
            default => VitalFlag::Normal,
        };
    }

    private function temperature(?float $value): VitalFlag
    {
        if ($value === null) {
            return VitalFlag::Unknown;
        }

        return match (true) {
            $value < 35 => VitalFlag::Low,
            $value >= 40 => VitalFlag::VeryHigh,
            $value >= 38 => VitalFlag::High,
            default => VitalFlag::Normal,
        };
    }
}
