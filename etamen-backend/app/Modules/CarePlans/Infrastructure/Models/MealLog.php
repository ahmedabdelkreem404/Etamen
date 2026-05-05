<?php

namespace App\Modules\CarePlans\Infrastructure\Models;

use App\Models\User;
use App\Modules\CarePlans\Domain\Enums\CarePlanMealType;
use App\Modules\CarePlans\Domain\Enums\MealLogStatus;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealLog extends Model
{
    protected $fillable = [
        'care_plan_id',
        'care_plan_meal_id',
        'patient_user_id',
        'logged_at',
        'meal_type',
        'status',
        'description',
        'photo_file_id',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
            'meal_type' => CarePlanMealType::class,
            'status' => MealLogStatus::class,
            'metadata' => 'array',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'care_plan_id');
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(CarePlanMeal::class, 'care_plan_meal_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'photo_file_id');
    }
}
