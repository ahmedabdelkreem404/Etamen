<?php

namespace App\Modules\Health\Http\Requests;

use App\Modules\Health\Domain\Enums\VitalType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class VitalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vital_type' => ['required', Rule::in(VitalType::values())],
            'measured_at' => ['required', 'date', 'before_or_equal:'.now()->addMinutes(10)->toDateTimeString()],
            'value_decimal' => ['nullable', 'numeric'],
            'value_secondary_decimal' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
            'patient_user_id' => ['prohibited'],
            'source' => ['prohibited'],
            'flag' => ['prohibited'],
            'unit' => ['prohibited'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = $this->input('vital_type');
                $value = $this->input('value_decimal');
                $secondary = $this->input('value_secondary_decimal');

                if ($type === VitalType::BloodPressure->value && ($value === null || $secondary === null)) {
                    $validator->errors()->add('value_decimal', 'Blood pressure requires systolic and diastolic values.');
                }

                if (in_array($type, [
                    VitalType::BloodSugar->value,
                    VitalType::HeartRate->value,
                    VitalType::OxygenSaturation->value,
                    VitalType::Temperature->value,
                    VitalType::Weight->value,
                    VitalType::Sleep->value,
                ], true) && $value === null) {
                    $validator->errors()->add('value_decimal', 'This vital type requires a numeric value.');
                }

                if ($type === VitalType::BloodSugar->value && ! in_array(data_get($this->input('metadata', []), 'context', 'unknown'), [
                    'fasting',
                    'after_meal',
                    'random',
                    'before_sleep',
                    'unknown',
                ], true)) {
                    $validator->errors()->add('metadata.context', 'Invalid blood sugar context.');
                }

                if ($type === VitalType::Sleep->value && data_get($this->input('metadata', []), 'quality') && ! in_array(data_get($this->input('metadata', []), 'quality'), [
                    'poor',
                    'fair',
                    'good',
                    'excellent',
                ], true)) {
                    $validator->errors()->add('metadata.quality', 'Invalid sleep quality.');
                }

                if ($type === VitalType::Mood->value && ! in_array(data_get($this->input('metadata', []), 'mood'), [
                    'very_bad',
                    'bad',
                    'neutral',
                    'good',
                    'very_good',
                ], true)) {
                    $validator->errors()->add('metadata.mood', 'Mood records require a valid mood value.');
                }

                if ($type === VitalType::Symptom->value && ! $this->filled('notes') && empty(data_get($this->input('metadata', []), 'symptoms'))) {
                    $validator->errors()->add('notes', 'Symptom records require notes or symptoms metadata.');
                }
            },
        ];
    }
}
