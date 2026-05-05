<?php

namespace App\Modules\AI\Infrastructure\Providers;

use App\Modules\AI\Application\DTOs\AiProviderResponse;
use App\Modules\AI\Domain\Enums\AiProvider;
use Illuminate\Support\Facades\Http;

class DeepSeekProvider implements AiProviderInterface
{
    public function send(array $messages, array $options = []): AiProviderResponse
    {
        $apiKey = config('ai.deepseek.api_key');
        if (! $apiKey) {
            throw new ProviderUnavailableException('DeepSeek API key is not configured.');
        }

        $model = $options['model'] ?? config('ai.deepseek.model');
        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->retry(1, 200)
            ->post(rtrim(config('ai.deepseek.base_url'), '/').'/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => config('ai.max_tokens_per_response'),
            ]);

        if (! $response->successful()) {
            throw new ProviderUnavailableException('DeepSeek provider request failed.');
        }

        $json = $response->json();

        return new AiProviderResponse(
            content: (string) data_get($json, 'choices.0.message.content', ''),
            provider: AiProvider::DeepSeek,
            model: $model,
            promptTokens: data_get($json, 'usage.prompt_tokens'),
            completionTokens: data_get($json, 'usage.completion_tokens'),
            totalTokens: data_get($json, 'usage.total_tokens'),
            rawMetadata: [
                'provider_message_id' => data_get($json, 'id'),
                'finish_reason' => data_get($json, 'choices.0.finish_reason'),
            ],
        );
    }
}
