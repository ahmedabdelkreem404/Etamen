<?php

namespace App\Filament\Resources\RadiologyScanCategoryResource\Pages;

use App\Filament\Resources\RadiologyScanCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRadiologyScanCategories extends ManageRecords
{
    protected static string $resource = RadiologyScanCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
