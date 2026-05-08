<?php

namespace App\Filament\Resources\RadiologyProfileResource\Pages;

use App\Filament\Resources\RadiologyProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRadiologyProfiles extends ManageRecords
{
    protected static string $resource = RadiologyProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
