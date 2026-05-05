<?php

namespace App\Modules\Health\Infrastructure\Models;

use App\Models\User;
use App\Modules\Health\Domain\Enums\HealthGoalStatus;
use App\Modules\Health\Domain\Enums\HealthGoalType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthGoal extends Model
{
    protected $fillable = [
        'patient_user_id',
        'goal_type',
        'title',
        'target_value',
        'unit',
        'target_date',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'goal_type' => HealthGoalType::class,
            'target_date' => 'date',
            'status' => HealthGoalStatus::class,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
