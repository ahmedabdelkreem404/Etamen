<?php

namespace App\Modules\Notifications\Application\Providers;

use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;

class SmsNotificationProvider implements NotificationProviderInterface
{
    public function send(NotificationDispatch $dispatch): void
    {
        throw new ProviderUnavailableException('SMS notification provider is not configured in this sprint.');
    }
}
