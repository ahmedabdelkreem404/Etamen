<?php

namespace App\Filament\Resources\MedicalCenterProfileResource\Pages;

use App\Filament\Resources\MedicalCenterProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMedicalCenterProfiles extends ManageRecords
{
    protected static string $resource = MedicalCenterProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
