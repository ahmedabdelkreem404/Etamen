<?php

namespace App\Filament\Resources\HomeHealthcareProfileResource\Pages;

use App\Filament\Resources\HomeHealthcareProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHomeHealthcareProfiles extends ManageRecords
{
    protected static string $resource = HomeHealthcareProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
