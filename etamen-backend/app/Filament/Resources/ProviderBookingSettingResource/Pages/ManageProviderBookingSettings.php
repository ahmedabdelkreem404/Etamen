<?php

namespace App\Filament\Resources\ProviderBookingSettingResource\Pages;

use App\Filament\Resources\ProviderBookingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProviderBookingSettings extends ManageRecords
{
    protected static string $resource = ProviderBookingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
