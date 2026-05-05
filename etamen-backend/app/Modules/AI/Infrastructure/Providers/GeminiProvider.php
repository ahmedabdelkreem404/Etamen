<?php

namespace App\Modules\AI\Infrastructure\Providers;

use App\Modules\AI\Application\DTOs\AiProviderResponse;
use App\Modules\AI\Domain\Enums\AiProvider;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AiProviderInterface
{
    public function send(array $messages, array $options = []): AiProviderResponse
    {
        $apiKey = config('ai.gemini.api_key');
        if (! $apiKey) {
            throw new ProviderUnavailableException('Gemini API key is not configured.');
        }

        $model = $options['model'] ?? config('ai.gemini.model');
        $contents = collect($messages)
            ->reject(fn (array $message) => $message['role'] === 'system')
            ->map(fn (array $message) => [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ])
            ->values()
            ->all();
        $systemInstruction = collect($messages)->firstWhere('role', 'system')['content'] ?? null;

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => config('ai.max_tokens_per_response'),
            ],
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemInstruction]]];
        }

        $response = Http::timeout(20)
            ->retry(1, 200)
            ->post(rtrim(config('ai.gemini.base_url'), '/')."/v1beta/models/{$model}:generateContent?key={$apiKey}", $payload);

        if (! $response->successful()) {
            throw new ProviderUnavailableException('Gemini provider request failed.');
        }

        $json = $response->json();

        return new AiProviderResponse(
            content: (string) data_get($json, 'candidates.0.content.parts.0.text', ''),
            provider: AiProvider::Gemini,
            model: $model,
            promptTokens: data_get($json, 'usageMetadata.promptTokenCount'),
            completionTokens: data_get($json, 'usageMetadata.candidatesTokenCount'),
            totalTokens: data_get($json, 'usageMetadata.totalTokenCount'),
            rawMetadata: [
                'finish_reason' => data_get($json, 'candidates.0.finishReason'),
            ],
        );
    }
}
