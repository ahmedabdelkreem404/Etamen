<?php

namespace App\Modules\Medications\Infrastructure\Models;

use App\Models\User;
use App\Modules\Medications\Domain\Enums\MedicationRefillEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationRefillEvent extends Model
{
    protected $fillable = [
        'medication_reminder_id',
        'patient_user_id',
        'event_type',
        'event_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => MedicationRefillEventType::class,
            'event_date' => 'date',
        ];
    }

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(MedicationReminder::class, 'medication_reminder_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
