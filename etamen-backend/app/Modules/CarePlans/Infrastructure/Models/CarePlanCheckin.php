<?php

namespace App\Modules\CarePlans\Infrastructure\Models;

use App\Models\User;
use App\Modules\CarePlans\Domain\Enums\CarePlanMood;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarePlanCheckin extends Model
{
    protected $fillable = [
        'care_plan_id',
        'patient_user_id',
        'checkin_date',
        'commitment_score',
        'energy_level',
        'hunger_level',
        'sleep_quality',
        'mood',
        'symptoms_notes',
        'general_notes',
    ];

    protected function casts(): array
    {
        return [
            'checkin_date' => 'date',
            'commitment_score' => 'integer',
            'energy_level' => 'integer',
            'hunger_level' => 'integer',
            'sleep_quality' => 'integer',
            'mood' => CarePlanMood::class,
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'care_plan_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
