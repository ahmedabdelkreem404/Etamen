<?php

namespace App\Modules\Notifications\Application\Providers;

use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;

class FakeNotificationProvider implements NotificationProviderInterface
{
    public static int $calls = 0;

    public static array $lastPayloads = [];

    public function send(NotificationDispatch $dispatch): void
    {
        self::$calls++;
        self::$lastPayloads[] = $dispatch->payload ?? [];
    }
}
