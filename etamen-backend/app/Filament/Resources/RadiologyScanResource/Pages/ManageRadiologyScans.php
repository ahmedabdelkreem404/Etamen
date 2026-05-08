<?php

namespace App\Filament\Resources\RadiologyScanResource\Pages;

use App\Filament\Resources\RadiologyScanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRadiologyScans extends ManageRecords
{
    protected static string $resource = RadiologyScanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
