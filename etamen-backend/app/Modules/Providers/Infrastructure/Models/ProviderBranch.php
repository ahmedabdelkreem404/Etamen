<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Locations\Infrastructure\Models\Area;
use App\Modules\Locations\Infrastructure\Models\City;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderBranch extends Model
{
    protected $fillable = [
        'provider_id',
        'city_id',
        'area_id',
        'name_ar',
        'name_en',
        'phone',
        'whatsapp',
        'address_line_1',
        'address_line_2',
        'district',
        'address_ar',
        'address_en',
        'latitude',
        'longitude',
        'working_hours_json',
        'is_24_hours',
        'home_service_radius_km',
        'delivery_radius_km',
        'is_main',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'working_hours_json' => 'array',
            'is_24_hours' => 'boolean',
            'home_service_radius_km' => 'decimal:2',
            'delivery_radius_km' => 'decimal:2',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
