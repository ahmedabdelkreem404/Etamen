<?php

namespace App\Filament\Resources\RadiologyPreparationInstructionResource\Pages;

use App\Filament\Resources\RadiologyPreparationInstructionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRadiologyPreparationInstructions extends ManageRecords
{
    protected static string $resource = RadiologyPreparationInstructionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
