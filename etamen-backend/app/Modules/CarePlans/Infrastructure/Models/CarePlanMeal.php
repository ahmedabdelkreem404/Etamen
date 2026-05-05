<?php

namespace App\Modules\CarePlans\Infrastructure\Models;

use App\Modules\CarePlans\Domain\Enums\CarePlanMealType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarePlanMeal extends Model
{
    protected $fillable = [
        'care_plan_day_id',
        'meal_type',
        'title',
        'description',
        'calories',
        'protein_g',
        'carbs_g',
        'fat_g',
        'instructions',
        'sort_order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'meal_type' => CarePlanMealType::class,
            'calories' => 'integer',
            'protein_g' => 'decimal:2',
            'carbs_g' => 'decimal:2',
            'fat_g' => 'decimal:2',
            'sort_order' => 'integer',
            'is_required' => 'boolean',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(CarePlanDay::class, 'care_plan_day_id');
    }

    public function mealLogs(): HasMany
    {
        return $this->hasMany(MealLog::class);
    }
}
