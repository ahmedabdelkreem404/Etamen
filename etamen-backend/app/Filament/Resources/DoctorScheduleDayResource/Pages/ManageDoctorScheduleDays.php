<?php

namespace App\Filament\Resources\DoctorScheduleDayResource\Pages;

use App\Filament\Resources\DoctorScheduleDayResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDoctorScheduleDays extends ManageRecords
{
    protected static string $resource = DoctorScheduleDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
