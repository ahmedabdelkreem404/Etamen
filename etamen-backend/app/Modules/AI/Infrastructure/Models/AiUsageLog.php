<?php

namespace App\Modules\AI\Infrastructure\Models;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'patient_user_id',
        'conversation_id',
        'provider',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'latency_ms',
        'success',
        'error_code',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'provider' => AiProvider::class,
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'latency_ms' => 'integer',
            'success' => 'boolean',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
