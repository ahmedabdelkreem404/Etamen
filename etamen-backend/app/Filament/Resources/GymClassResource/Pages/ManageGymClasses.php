<?php

namespace App\Filament\Resources\GymClassResource\Pages;

use App\Filament\Resources\GymClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGymClasses extends ManageRecords
{
    protected static string $resource = GymClassResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
