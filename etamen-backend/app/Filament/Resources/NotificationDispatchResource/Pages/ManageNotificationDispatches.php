<?php

namespace App\Filament\Resources\NotificationDispatchResource\Pages;

use App\Filament\Resources\NotificationDispatchResource;
use Filament\Resources\Pages\ManageRecords;

class ManageNotificationDispatches extends ManageRecords
{
    protected static string $resource = NotificationDispatchResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
