<?php

namespace App\Modules\Appointments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_profile_id' => ['prohibited'],
            'provider_id' => ['prohibited'],
            'branch_id' => ['nullable', 'integer', 'exists:provider_branches,id'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'slot_duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:240'],
            'buffer_minutes' => ['sometimes', 'integer', 'min:0', 'max:120'],
            'max_days_ahead' => ['sometimes', 'integer', 'min:1', 'max:60'],
        ];
    }
}
