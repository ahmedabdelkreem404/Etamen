<?php

namespace App\Modules\Medications\Infrastructure\Models;

use App\Models\User;
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationLog extends Model
{
    protected $fillable = [
        'medication_reminder_id',
        'patient_user_id',
        'scheduled_for',
        'action',
        'taken_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'action' => MedicationLogAction::class,
            'taken_at' => 'datetime',
            'metadata' => 'array',
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
