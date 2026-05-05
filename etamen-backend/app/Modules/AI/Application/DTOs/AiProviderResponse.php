<?php

namespace App\Modules\AI\Application\DTOs;

use App\Modules\AI\Domain\Enums\AiProvider;

class AiProviderResponse
{
    public function __construct(
        public readonly string $content,
        public readonly AiProvider $provider,
        public readonly ?string $model = null,
        public readonly ?int $promptTokens = null,
        public readonly ?int $completionTokens = null,
        public readonly ?int $totalTokens = null,
        public readonly array $rawMetadata = [],
    ) {}
}
