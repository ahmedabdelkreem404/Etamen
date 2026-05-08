<?php

namespace App\Filament\Resources\HospitalProfileResource\Pages;

use App\Filament\Resources\HospitalProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHospitalProfiles extends ManageRecords
{
    protected static string $resource = HospitalProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
