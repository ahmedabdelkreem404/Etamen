<?php

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Infrastructure\Providers\AiProviderInterface;
use App\Modules\AI\Infrastructure\Providers\DeepSeekProvider;
use App\Modules\AI\Infrastructure\Providers\FakeAiProvider;
use App\Modules\AI\Infrastructure\Providers\GeminiProvider;
use App\Modules\AI\Infrastructure\Providers\ProviderUnavailableException;

class AiProviderManager
{
    public function defaultProvider(): AiProvider
    {
        $configured = config('ai.default_provider', AiProvider::DeepSeek->value);

        return AiProvider::tryFrom((string) $configured) ?? AiProvider::DeepSeek;
    }

    public function provider(AiProvider $provider): AiProviderInterface
    {
        return match ($provider) {
            AiProvider::DeepSeek => app(DeepSeekProvider::class),
            AiProvider::Gemini => app(GeminiProvider::class),
            AiProvider::Fake => $this->fakeProvider(),
        };
    }

    private function fakeProvider(): AiProviderInterface
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new ProviderUnavailableException('Fake AI provider is not allowed outside local/testing environments.');
        }

        return app(FakeAiProvider::class);
    }
}
