<?php

namespace App\Filament\Resources\SpecialtyResource\Pages;

use App\Filament\Resources\SpecialtyResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSpecialties extends ManageRecords
{
    protected static string $resource = SpecialtyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
