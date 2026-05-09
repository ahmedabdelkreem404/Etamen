<?php

namespace App\Modules\Fitness\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCoachBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coach_provider_id' => ['required', 'integer', 'exists:providers,id'],
            'session_type_id' => ['required', 'integer', 'exists:coach_session_types,id'],
            'availability_slot_id' => ['nullable', 'integer', 'exists:coach_availability_slots,id'],
            'patient_goal' => ['nullable', 'string', 'max:2000'],
            'total_amount' => ['prohibited'],
            'status' => ['prohibited'],
            'payment_id' => ['prohibited'],
        ];
    }
}
