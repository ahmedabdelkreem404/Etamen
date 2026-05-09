<?php

namespace App\Filament\Resources\CoachPackageResource\Pages;

use App\Filament\Resources\CoachPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCoachPackages extends ManageRecords
{
    protected static string $resource = CoachPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
