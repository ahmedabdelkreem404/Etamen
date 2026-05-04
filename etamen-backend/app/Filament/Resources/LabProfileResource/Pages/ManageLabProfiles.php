<?php

namespace App\Filament\Resources\LabProfileResource\Pages;

use App\Filament\Resources\LabProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLabProfiles extends ManageRecords
{
    protected static string $resource = LabProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
