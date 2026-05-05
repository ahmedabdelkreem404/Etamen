<?php

namespace App\Modules\Notifications\Application\Providers;

use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;

class WhatsAppNotificationProvider implements NotificationProviderInterface
{
    public function send(NotificationDispatch $dispatch): void
    {
        throw new ProviderUnavailableException('WhatsApp notification provider is not configured in this sprint.');
    }
}
