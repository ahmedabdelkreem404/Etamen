<?php

namespace App\Modules\Fitness\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGymBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'branch_id' => ['nullable', 'integer', 'exists:provider_branches,id'],
            'membership_plan_id' => ['nullable', 'integer', 'exists:gym_membership_plans,id', 'required_without:gym_class_id', 'prohibits:gym_class_id'],
            'gym_class_id' => ['nullable', 'integer', 'exists:gym_classes,id', 'required_without:membership_plan_id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'total_amount' => ['prohibited'],
            'status' => ['prohibited'],
            'payment_id' => ['prohibited'],
        ];
    }
}
