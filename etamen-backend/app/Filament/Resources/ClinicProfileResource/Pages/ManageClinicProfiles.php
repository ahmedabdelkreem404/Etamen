<?php

namespace App\Filament\Resources\ClinicProfileResource\Pages;

use App\Filament\Resources\ClinicProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageClinicProfiles extends ManageRecords
{
    protected static string $resource = ClinicProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
