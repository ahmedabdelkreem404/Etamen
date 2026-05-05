<?php

namespace App\Modules\Medications\Http\Requests;

use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicationLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'action' => ['required', Rule::in([MedicationLogAction::Taken->value, MedicationLogAction::Skipped->value])],
            'scheduled_for' => ['required', 'date'],
            'taken_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
