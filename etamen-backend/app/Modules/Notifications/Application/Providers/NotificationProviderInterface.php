<?php

namespace App\Modules\Notifications\Application\Providers;

use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;

interface NotificationProviderInterface
{
    public function send(NotificationDispatch $dispatch): void;
}
