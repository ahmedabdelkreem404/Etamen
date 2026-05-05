<?php

namespace App\Modules\CarePlans\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarePlanDay extends Model
{
    protected $fillable = [
        'care_plan_id',
        'day_number',
        'day_date',
        'title',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
            'day_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'care_plan_id');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(CarePlanMeal::class)->orderBy('sort_order');
    }
}
