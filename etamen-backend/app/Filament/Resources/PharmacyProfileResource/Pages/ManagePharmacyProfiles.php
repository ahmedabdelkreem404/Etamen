<?php

namespace App\Filament\Resources\PharmacyProfileResource\Pages;

use App\Filament\Resources\PharmacyProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePharmacyProfiles extends ManageRecords
{
    protected static string $resource = PharmacyProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
