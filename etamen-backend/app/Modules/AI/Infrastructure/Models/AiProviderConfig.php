<?php

namespace App\Modules\AI\Infrastructure\Models;

use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use Illuminate\Database\Eloquent\Model;

class AiProviderConfig extends Model
{
    protected $fillable = [
        'provider',
        'is_active',
        'model',
        'encrypted_config',
        'safety_level',
    ];

    protected function casts(): array
    {
        return [
            'provider' => AiProvider::class,
            'is_active' => 'boolean',
            'encrypted_config' => 'encrypted:array',
            'safety_level' => AiSafetyLevel::class,
        ];
    }
}
