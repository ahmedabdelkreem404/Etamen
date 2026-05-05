<?php

namespace App\Filament\Resources\PharmacyProductResource\Pages;

use App\Filament\Resources\PharmacyProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePharmacyProducts extends ManageRecords
{
    protected static string $resource = PharmacyProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
