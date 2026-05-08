<?php

namespace App\Filament\Resources\HospitalDepartmentResource\Pages;

use App\Filament\Resources\HospitalDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHospitalDepartments extends ManageRecords
{
    protected static string $resource = HospitalDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
