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
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'address_ar' => ['nullable', 'string'],
            'address_en' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
