<?php

namespace App\Modules\Radiology\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyScanCategory extends Model
{
    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(RadiologyScan::class);
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(RadiologyPreparationInstruction::class);
    }
}
