<?php

namespace App\Modules\AI\Infrastructure\Models;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiConversationStatus;
use App\Modules\AI\Domain\Enums\AiLanguage;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    protected $fillable = [
        'patient_user_id',
        'title',
        'status',
        'provider',
        'language',
        'context_enabled',
        'safety_level',
        'last_message_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => AiConversationStatus::class,
            'provider' => AiProvider::class,
            'language' => AiLanguage::class,
            'context_enabled' => 'boolean',
            'safety_level' => AiSafetyLevel::class,
            'last_message_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }

    public function safetyEvents(): HasMany
    {
        return $this->hasMany(AiSafetyEvent::class, 'conversation_id');
    }
}
