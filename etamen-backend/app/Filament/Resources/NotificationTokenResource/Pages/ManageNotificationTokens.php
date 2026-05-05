<?php

namespace App\Filament\Resources\NotificationTokenResource\Pages;

use App\Filament\Resources\NotificationTokenResource;
use Filament\Resources\Pages\ManageRecords;

class ManageNotificationTokens extends ManageRecords
{
    protected static string $resource = NotificationTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
