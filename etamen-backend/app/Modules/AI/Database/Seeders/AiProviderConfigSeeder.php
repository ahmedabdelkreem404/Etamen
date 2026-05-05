<?php

namespace App\Modules\AI\Database\Seeders;

use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use App\Modules\AI\Infrastructure\Models\AiProviderConfig;
use Illuminate\Database\Seeder;

class AiProviderConfigSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([AiProvider::DeepSeek, AiProvider::Gemini] as $provider) {
            AiProviderConfig::query()->firstOrCreate(
                ['provider' => $provider],
                [
                    'is_active' => false,
                    'model' => config('ai.'.$provider->value.'.model'),
                    'encrypted_config' => [],
                    'safety_level' => AiSafetyLevel::Strict,
                ],
            );
        }
    }
}
