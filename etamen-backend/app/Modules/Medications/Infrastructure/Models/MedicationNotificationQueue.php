<?php

namespace App\Modules\Medications\Infrastructure\Models;

use App\Models\User;
use App\Modules\Medications\Domain\Enums\MedicationNotificationChannel;
use App\Modules\Medications\Domain\Enums\MedicationNotificationStatus;
use App\Modules\Medications\Domain\Enums\MedicationNotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationNotificationQueue extends Model
{
    protected $table = 'medication_notification_queue';

    protected $fillable = [
        'medication_reminder_id',
        'patient_user_id',
        'scheduled_for',
        'notification_type',
        'status',
        'channel',
        'payload',
        'attempted_at',
        'sent_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'notification_type' => MedicationNotificationType::class,
            'status' => MedicationNotificationStatus::class,
            'channel' => MedicationNotificationChannel::class,
            'payload' => 'array',
            'attempted_at' => 'datetime',
            'sent_at' => 'datetime',
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
