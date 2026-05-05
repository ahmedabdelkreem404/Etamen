<?php

namespace App\Filament\Resources\NotificationPreferenceResource\Pages;

use App\Filament\Resources\NotificationPreferenceResource;
use Filament\Resources\Pages\ManageRecords;

class ManageNotificationPreferences extends ManageRecords
{
    protected static string $resource = NotificationPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
