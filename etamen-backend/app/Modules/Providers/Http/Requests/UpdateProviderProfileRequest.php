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
        ];
    }
}
