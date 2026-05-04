<?php

namespace App\Filament\Resources\PatientProfileResource\Pages;

use App\Filament\Resources\PatientProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePatientProfiles extends ManageRecords
{
    protected static string $resource = PatientProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
