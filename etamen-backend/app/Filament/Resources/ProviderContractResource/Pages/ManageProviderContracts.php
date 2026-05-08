<?php

namespace App\Filament\Resources\ProviderContractResource\Pages;

use App\Filament\Resources\ProviderContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProviderContracts extends ManageRecords
{
    protected static string $resource = ProviderContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
