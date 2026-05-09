<?php

namespace App\Modules\Appointments\Http\Requests;

use App\Modules\Appointments\Domain\Enums\ConsultationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'provider_id' => ['prohibited'],
            'price' => ['prohibited'],
            'status' => ['prohibited'],
            'payment_id' => ['prohibited'],
            'avatar_url' => ['prohibited'],
            'avatar_path' => ['prohibited'],
            'rating_average' => ['prohibited'],
            'reviews_count' => ['prohibited'],
            'doctor_profile_id' => ['required', 'integer', 'exists:doctor_profiles,id'],
            'appointment_slot_id' => ['required', 'integer', 'exists:appointment_slots,id'],
            'hospital_provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'hospital_department_id' => ['nullable', 'integer', 'exists:hospital_departments,id'],
            'hospital_doctor_id' => ['nullable', 'integer', 'exists:hospital_doctors,id'],
            'consultation_type' => ['required', Rule::in(ConsultationType::values())],
            'problem_description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
