<?php

namespace App\Modules\Appointments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_profile_id' => ['nullable', 'integer', 'exists:doctor_profiles,id'],
            'branch_id' => ['nullable', 'integer', 'exists:provider_branches,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'slot_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'buffer_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'max_days_ahead' => ['nullable', 'integer', 'min:1', 'max:60'],
        ];
    }
}
