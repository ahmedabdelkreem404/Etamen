<?php

namespace App\Filament\Resources\LabPackageResource\Pages;

use App\Filament\Resources\LabPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLabPackages extends ManageRecords
{
    protected static string $resource = LabPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
