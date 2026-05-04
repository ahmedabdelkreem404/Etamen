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
        'address_ar',
        'address_en',
        'latitude',
        'longitude',
        'is_main',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
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
