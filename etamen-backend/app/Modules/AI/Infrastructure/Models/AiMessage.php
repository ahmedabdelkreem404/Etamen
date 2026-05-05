<?php

namespace App\Modules\AI\Infrastructure\Models;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiMessageRole;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'patient_user_id',
        'role',
        'content',
        'safety_classification',
        'was_refused',
        'provider',
        'provider_message_id',
        'token_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'role' => AiMessageRole::class,
            'safety_classification' => AiSafetyClassification::class,
            'was_refused' => 'boolean',
            'provider' => AiProvider::class,
            'token_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
