<?php

namespace App\Filament\Resources\ProviderServiceResource\Pages;

use App\Filament\Resources\ProviderServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProviderServices extends ManageRecords
{
    protected static string $resource = ProviderServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
