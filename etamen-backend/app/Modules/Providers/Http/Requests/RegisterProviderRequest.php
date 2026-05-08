<?php

namespace App\Modules\Providers\Http\Requests;

use App\Modules\Providers\Domain\Enums\CoachType;
use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'provider_type' => ['required', Rule::in(ProviderType::values())],
            'provider_name_ar' => ['nullable', 'string', 'max:255'],
            'provider_name_en' => ['required', 'string', 'max:255'],
            'provider_email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'profile' => ['nullable', 'array'],
            'profile.license_number' => ['nullable', 'string', 'max:255'],
            'profile.clinic_type' => ['nullable', 'string', 'max:120'],
            'profile.center_type' => ['nullable', 'string', 'max:120'],
            'profile.coach_type' => ['nullable', Rule::in(CoachType::values())],
            'profile.experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'profile.session_price' => ['nullable', 'numeric', 'min:0'],
            'profile.monthly_followup_price' => ['nullable', 'numeric', 'min:0'],
            'profile.service_radius_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'profile.certifications_summary' => ['nullable', 'string'],
            'profile.description_ar' => ['nullable', 'string'],
            'profile.description_en' => ['nullable', 'string'],
            'profile.emergency_available' => ['nullable', 'boolean'],
            'profile.has_inpatient' => ['nullable', 'boolean'],
            'profile.has_outpatient' => ['nullable', 'boolean'],
            'profile.has_icu' => ['nullable', 'boolean'],
            'profile.has_ambulance' => ['nullable', 'boolean'],
            'profile.home_service_enabled' => ['nullable', 'boolean'],
            'profile.report_delivery_enabled' => ['nullable', 'boolean'],
            'profile.dicom_supported' => ['nullable', 'boolean'],
            'profile.men_allowed' => ['nullable', 'boolean'],
            'profile.women_allowed' => ['nullable', 'boolean'],
            'profile.ladies_only_hours' => ['nullable', 'boolean'],
            'profile.has_classes' => ['nullable', 'boolean'],
            'profile.has_personal_training' => ['nullable', 'boolean'],
            'profile.online_coaching_enabled' => ['nullable', 'boolean'],
            'profile.gym_visit_enabled' => ['nullable', 'boolean'],
            'profile.home_training_enabled' => ['nullable', 'boolean'],
            'profile.home_visit_enabled' => ['nullable', 'boolean'],
            'profile.center_visit_enabled' => ['nullable', 'boolean'],
            'profile.nursing_enabled' => ['nullable', 'boolean'],
            'profile.injections_enabled' => ['nullable', 'boolean'],
            'profile.wound_care_enabled' => ['nullable', 'boolean'],
            'profile.elderly_care_enabled' => ['nullable', 'boolean'],
            'profile.physiotherapy_home_enabled' => ['nullable', 'boolean'],
            ...ProviderBranchRules::optional(),
            'approval_notes' => ['nullable', 'string'],
        ];
    }
}
