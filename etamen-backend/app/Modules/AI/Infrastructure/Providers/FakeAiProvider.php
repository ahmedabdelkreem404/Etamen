<?php

namespace App\Modules\AI\Infrastructure\Providers;

use App\Modules\AI\Application\DTOs\AiProviderResponse;
use App\Modules\AI\Domain\Enums\AiProvider;

class FakeAiProvider implements AiProviderInterface
{
    public static int $calls = 0;

    public static array $lastMessages = [];

    public function send(array $messages, array $options = []): AiProviderResponse
    {
        self::$calls++;
        self::$lastMessages = $messages;

        return new AiProviderResponse(
            content: 'رد آمن للتنظيم والفهم العام فقط. أنا لست طبيبًا ولا أستطيع التشخيص أو وصف علاج.',
            provider: AiProvider::Fake,
            model: 'fake-test-model',
            promptTokens: 10,
            completionTokens: 12,
            totalTokens: 22,
            rawMetadata: ['test_provider' => true],
        );
    }
}
