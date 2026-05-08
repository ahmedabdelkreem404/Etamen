<?php

namespace App\Filament\Resources\PhysiotherapyProfileResource\Pages;

use App\Filament\Resources\PhysiotherapyProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePhysiotherapyProfiles extends ManageRecords
{
    protected static string $resource = PhysiotherapyProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
