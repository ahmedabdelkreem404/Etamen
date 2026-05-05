<?php

namespace App\Modules\AI\Application\Services;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use App\Modules\AI\Infrastructure\Models\AiProviderConfig;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AiProviderConfigService
{
    public function __construct(private readonly AuditLogService $auditLogs) {}

    public function ensureDefaults(): Collection
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

        return AiProviderConfig::query()->orderBy('provider')->get();
    }

    public function update(User $admin, AiProviderConfig $config, array $data): AiProviderConfig
    {
        return DB::transaction(function () use ($admin, $config, $data): AiProviderConfig {
            $before = $config->getAttributes();

            $payload = [];
            foreach (['is_active', 'model', 'encrypted_config', 'safety_level'] as $field) {
                if (array_key_exists($field, $data)) {
                    $payload[$field] = $data[$field];
                }
            }

            if (isset($payload['safety_level']) && is_string($payload['safety_level'])) {
                $payload['safety_level'] = AiSafetyLevel::from($payload['safety_level']);
            }

            $config->fill($payload)->save();
            $this->auditLogs->log('ai_provider_config.updated', $config, $admin, before: $before, after: $config->getAttributes());

            return $config->refresh();
        });
    }
}
