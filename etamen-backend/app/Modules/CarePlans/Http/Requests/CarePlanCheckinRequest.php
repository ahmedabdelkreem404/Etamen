<?php

namespace App\Modules\CarePlans\Http\Requests;

use App\Modules\CarePlans\Domain\Enums\CarePlanMood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarePlanCheckinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'checkin_date' => ['required', 'date'],
            'commitment_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'energy_level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'hunger_level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'sleep_quality' => ['nullable', 'integer', 'min:1', 'max:5'],
            'mood' => ['nullable', Rule::in(CarePlanMood::values())],
            'symptoms_notes' => ['nullable', 'string', 'max:3000'],
            'general_notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
