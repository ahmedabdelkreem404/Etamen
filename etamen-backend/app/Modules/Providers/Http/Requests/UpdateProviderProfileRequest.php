<?php

namespace App\Modules\Providers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'type' => ['prohibited'],
            'status' => ['prohibited'],
            'is_active' => ['prohibited'],
            'approved_at' => ['prohibited'],
            'rejected_at' => ['prohibited'],
            'suspended_at' => ['prohibited'],
            'booking_settings' => ['prohibited'],
            'contract' => ['prohibited'],
            'services' => ['prohibited'],
            'avatar_path' => ['prohibited'],
            'avatar_url' => ['prohibited'],
            'rating_average' => ['prohibited'],
            'reviews_count' => ['prohibited'],
            'name_ar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email:rfc', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'profile' => ['sometimes', 'array'],
            'profile.avatar_path' => ['prohibited'],
            'profile.avatar_url' => ['prohibited'],
            'profile.rating_average' => ['prohibited'],
            'profile.reviews_count' => ['prohibited'],
            'profile.title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'profile.bio_ar' => ['sometimes', 'nullable', 'string'],
            'profile.bio_en' => ['sometimes', 'nullable', 'string'],
            'profile.consultation_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'profile.years_of_experience' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:80'],
            'profile.specialty_ids' => ['sometimes', 'array'],
            'profile.specialty_ids.*' => ['integer', 'exists:specialties,id'],
            'profile.license_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'profile.delivery_available' => ['sometimes', 'boolean'],
            'profile.home_collection_available' => ['sometimes', 'boolean'],
            'profile.clinic_type' => ['sometimes', 'nullable', 'string', 'max:120'],
            'profile.center_type' => ['sometimes', 'nullable', 'string', 'max:120'],
            'profile.coach_type' => ['sometimes', 'nullable', 'string', 'max:120'],
            'profile.experience_years' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:80'],
            'profile.session_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'profile.monthly_followup_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'profile.service_radius_km' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:500'],
            'profile.certifications_summary' => ['sometimes', 'nullable', 'string'],
            'profile.emergency_available' => ['sometimes', 'boolean'],
            'profile.has_inpatient' => ['sometimes', 'boolean'],
            'profile.has_outpatient' => ['sometimes', 'boolean'],
            'profile.has_icu' => ['sometimes', 'boolean'],
            'profile.has_ambulance' => ['sometimes', 'boolean'],
            'profile.home_service_enabled' => ['sometimes', 'boolean'],
            'profile.report_delivery_enabled' => ['sometimes', 'boolean'],
            'profile.dicom_supported' => ['sometimes', 'boolean'],
            'profile.men_allowed' => ['sometimes', 'boolean'],
            'profile.women_allowed' => ['sometimes', 'boolean'],
            'profile.ladies_only_hours' => ['sometimes', 'boolean'],
            'profile.has_classes' => ['sometimes', 'boolean'],
            'profile.has_personal_training' => ['sometimes', 'boolean'],
            'profile.online_coaching_enabled' => ['sometimes', 'boolean'],
            'profile.gym_visit_enabled' => ['sometimes', 'boolean'],
            'profile.home_training_enabled' => ['sometimes', 'boolean'],
            'profile.home_visit_enabled' => ['sometimes', 'boolean'],
            'profile.center_visit_enabled' => ['sometimes', 'boolean'],
            'profile.nursing_enabled' => ['sometimes', 'boolean'],
            'profile.injections_enabled' => ['sometimes', 'boolean'],
            'profile.wound_care_enabled' => ['sometimes', 'boolean'],
            'profile.elderly_care_enabled' => ['sometimes', 'boolean'],
            'profile.physiotherapy_home_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
