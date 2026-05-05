<?php

namespace App\Modules\AI\Application\DTOs;

use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;

class AiSafetyDecision
{
    public function __construct(
        public readonly AiSafetyClassification $classification,
        public readonly bool $shouldRefuse,
        public readonly bool $shouldCallProvider,
        public readonly ?string $safeResponse = null,
        public readonly ?AiSafetyEventType $eventType = null,
        public readonly AiSafetySeverity $severity = AiSafetySeverity::Low,
    ) {}
}
