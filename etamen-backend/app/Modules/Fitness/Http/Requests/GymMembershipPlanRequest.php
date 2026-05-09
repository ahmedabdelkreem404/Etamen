<?php

namespace App\Modules\Fitness\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymMembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:provider_branches,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:366'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'sessions_count' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'includes_classes' => ['nullable', 'boolean'],
            'includes_personal_training' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'provider_id' => ['prohibited'],
        ];
    }
}
