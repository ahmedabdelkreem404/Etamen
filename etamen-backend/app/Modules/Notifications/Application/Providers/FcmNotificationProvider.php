<?php

namespace App\Modules\Notifications\Application\Providers;

use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;

class FcmNotificationProvider implements NotificationProviderInterface
{
    public function send(NotificationDispatch $dispatch): void
    {
        if (! config('services.fcm.server_key')) {
            throw new ProviderUnavailableException('FCM credentials are not configured.');
        }

        throw new ProviderUnavailableException('Live FCM sending is not enabled in this sprint.');
    }
}
