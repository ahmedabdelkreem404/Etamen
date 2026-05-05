<?php

namespace App\Modules\Medications\Http\Requests;

use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MedicationReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'patient_user_id' => ['prohibited'],
            'source' => ['prohibited'],
            'status' => ['prohibited'],
            'medication_name' => [$required, 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'dosage_unit' => ['nullable', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'frequency_type' => [$required, Rule::in(MedicationFrequencyType::values())],
            'interval_hours' => ['nullable', 'integer', 'min:1', 'max:24'],
            'start_date' => [$required, 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'timezone' => ['nullable', 'timezone'],
            'prescribed_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'refill_enabled' => ['nullable', 'boolean'],
            'refill_quantity' => ['nullable', 'integer', 'min:0'],
            'refill_threshold' => ['nullable', 'integer', 'min:0'],
            'refill_reminder_date' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
            'metadata.days_of_week' => ['nullable', 'array'],
            'metadata.days_of_week.*' => ['integer', 'between:0,6'],
            'times' => ['nullable', 'array', 'max:10'],
            'times.*.time_of_day' => ['required_with:times', 'date_format:H:i'],
            'times.*.label' => ['nullable', 'string', 'max:100'],
            'times.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = $this->input('frequency_type');
                if (! $type) {
                    return;
                }

                $times = $this->input('times', []);
                $count = is_array($times) ? count($times) : 0;

                match ($type) {
                    MedicationFrequencyType::OnceDaily->value => $this->requireTimeCount($validator, $count, 1),
                    MedicationFrequencyType::TwiceDaily->value => $this->requireTimeCount($validator, $count, 2),
                    MedicationFrequencyType::ThreeTimesDaily->value => $this->requireTimeCount($validator, $count, 3),
                    MedicationFrequencyType::CustomTimes->value => $this->requireTimeRange($validator, $count, 1, 10),
                    MedicationFrequencyType::SpecificDays->value => $this->requireSpecificDays($validator, $count),
                    MedicationFrequencyType::EveryXHours->value => $this->requireEveryHours($validator),
                    default => null,
                };
            },
        ];
    }

    private function requireTimeCount(Validator $validator, int $actual, int $expected): void
    {
        if ($actual !== $expected) {
            $validator->errors()->add('times', "This frequency requires exactly {$expected} reminder time(s).");
        }
    }

    private function requireTimeRange(Validator $validator, int $actual, int $min, int $max): void
    {
        if ($actual < $min || $actual > $max) {
            $validator->errors()->add('times', "This frequency requires between {$min} and {$max} reminder times.");
        }
    }

    private function requireEveryHours(Validator $validator): void
    {
        if (! $this->filled('interval_hours')) {
            $validator->errors()->add('interval_hours', 'Every X hours frequency requires interval_hours.');
        }
    }

    private function requireSpecificDays(Validator $validator, int $count): void
    {
        $days = data_get($this->input('metadata', []), 'days_of_week', []);
        if ($count < 1) {
            $validator->errors()->add('times', 'Specific days frequency requires at least one reminder time.');
        }

        if (! is_array($days) || count($days) < 1) {
            $validator->errors()->add('metadata.days_of_week', 'Specific days frequency requires days_of_week.');
        }
    }
}
