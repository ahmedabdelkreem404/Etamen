<?php

namespace App\Modules\Medications\Infrastructure\Models;

use App\Models\User;
use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Medications\Domain\Enums\MedicationReminderSource;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationReminder extends Model
{
    protected $fillable = [
        'patient_user_id',
        'medication_name',
        'dosage',
        'dosage_unit',
        'instructions',
        'frequency_type',
        'interval_hours',
        'start_date',
        'end_date',
        'timezone',
        'status',
        'prescribed_by',
        'notes',
        'refill_enabled',
        'refill_quantity',
        'refill_threshold',
        'refill_reminder_date',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'frequency_type' => MedicationFrequencyType::class,
            'interval_hours' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => MedicationReminderStatus::class,
            'refill_enabled' => 'boolean',
            'refill_quantity' => 'integer',
            'refill_threshold' => 'integer',
            'refill_reminder_date' => 'date',
            'source' => MedicationReminderSource::class,
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function times(): HasMany
    {
        return $this->hasMany(MedicationReminderTime::class)->orderBy('time_of_day');
    }

    public function activeTimes(): HasMany
    {
        return $this->times()->where('is_active', true);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MedicationLog::class);
    }

    public function refillEvents(): HasMany
    {
        return $this->hasMany(MedicationRefillEvent::class);
    }

    public function notificationQueue(): HasMany
    {
        return $this->hasMany(MedicationNotificationQueue::class);
    }
}
