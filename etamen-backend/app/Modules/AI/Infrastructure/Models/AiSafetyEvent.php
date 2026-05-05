<?php

namespace App\Modules\AI\Infrastructure\Models;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSafetyEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'message_id',
        'patient_user_id',
        'event_type',
        'severity',
        'description',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => AiSafetyEventType::class,
            'severity' => AiSafetySeverity::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class, 'message_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
