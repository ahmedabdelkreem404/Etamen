<?php

namespace App\Modules\AI\Infrastructure\Providers;

use App\Modules\AI\Application\DTOs\AiProviderResponse;

interface AiProviderInterface
{
    public function send(array $messages, array $options = []): AiProviderResponse;
}
