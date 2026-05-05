<?php

namespace App\Modules\Medications\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationReminderTime extends Model
{
    protected $fillable = [
        'medication_reminder_id',
        'time_of_day',
        'label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(MedicationReminder::class, 'medication_reminder_id');
    }
}
