<?php

namespace App\Modules\Providers\Http\Requests;

class ProviderBranchRules
{
    public static function optional(): array
    {
        return [
            'branch_name_ar' => ['nullable', 'string', 'max:255'],
            'branch_name_en' => ['nullable', 'string', 'max:255'],
            'branch_phone' => ['nullable', 'string', 'max:30'],
            'branch_whatsapp' => ['nullable', 'string', 'max:30'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'address_ar' => ['nullable', 'string'],
            'address_en' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'working_hours_json' => ['nullable', 'array'],
            'is_24_hours' => ['nullable', 'boolean'],
            'home_service_radius_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'delivery_radius_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
        ];
    }
}
