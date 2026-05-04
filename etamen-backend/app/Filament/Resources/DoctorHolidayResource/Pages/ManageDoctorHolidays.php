<?php

namespace App\Filament\Resources\DoctorHolidayResource\Pages;

use App\Filament\Resources\DoctorHolidayResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDoctorHolidays extends ManageRecords
{
    protected static string $resource = DoctorHolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
