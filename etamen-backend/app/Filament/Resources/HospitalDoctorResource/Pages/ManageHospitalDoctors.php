<?php

namespace App\Filament\Resources\HospitalDoctorResource\Pages;

use App\Filament\Resources\HospitalDoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHospitalDoctors extends ManageRecords
{
    protected static string $resource = HospitalDoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
