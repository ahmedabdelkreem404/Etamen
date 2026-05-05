<?php

namespace App\Modules\CarePlans\Infrastructure\Models;

use App\Modules\CarePlans\Domain\Enums\CarePlanFoodCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarePlanFoodItem extends Model
{
    protected $fillable = [
        'care_plan_id',
        'category',
        'name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'category' => CarePlanFoodCategory::class,
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'care_plan_id');
    }
}
