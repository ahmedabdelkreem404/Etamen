<?php

namespace App\Modules\AI\Infrastructure\Providers;

use App\Modules\AI\Application\DTOs\AiProviderResponse;
use App\Modules\AI\Domain\Enums\AiProvider;

class FakeAiProvider implements AiProviderInterface
{
    public static int $calls = 0;

    public static array $lastMessages = [];

    public static ?string $nextResponseContent = null;

    public static array $nextRawMetadata = [];

    public function send(array $messages, array $options = []): AiProviderResponse
    {
        self::$calls++;
        self::$lastMessages = $messages;

        $content = self::$nextResponseContent ?: 'رد آمن للتنظيم والفهم العام فقط. أنا لست طبيبًا ولا أستطيع التشخيص أو وصف علاج.';
        $rawMetadata = self::$nextRawMetadata ?: ['test_provider' => true];
        self::$nextResponseContent = null;
        self::$nextRawMetadata = [];

        return new AiProviderResponse(
            content: $content,
            provider: AiProvider::Fake,
            model: 'fake-test-model',
            promptTokens: 10,
            completionTokens: 12,
            totalTokens: 22,
            rawMetadata: $rawMetadata,
        );
    }
}
