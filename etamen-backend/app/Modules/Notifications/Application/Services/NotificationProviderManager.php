<?php

namespace App\Modules\Notifications\Application\Services;

use App\Modules\Notifications\Application\Providers\EmailNotificationProvider;
use App\Modules\Notifications\Application\Providers\FakeNotificationProvider;
use App\Modules\Notifications\Application\Providers\FcmNotificationProvider;
use App\Modules\Notifications\Application\Providers\NotificationProviderInterface;
use App\Modules\Notifications\Application\Providers\SmsNotificationProvider;
use App\Modules\Notifications\Application\Providers\WhatsAppNotificationProvider;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;

class NotificationProviderManager
{
    public function providerFor(NotificationChannel $channel, ?string $provider = null): NotificationProviderInterface
    {
        $configured = $provider ?: (string) config('notifications.default_provider', 'fake');

        if ($configured === 'fake' && app()->environment(['local', 'testing'])) {
            return app(FakeNotificationProvider::class);
        }

        return match ($channel) {
            NotificationChannel::Push => app(FcmNotificationProvider::class),
            NotificationChannel::Email => app(EmailNotificationProvider::class),
            NotificationChannel::Sms => app(SmsNotificationProvider::class),
            NotificationChannel::WhatsApp => app(WhatsAppNotificationProvider::class),
            NotificationChannel::InApp => app(FakeNotificationProvider::class),
        };
    }
}
