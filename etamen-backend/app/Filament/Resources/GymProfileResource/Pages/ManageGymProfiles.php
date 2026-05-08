<?php

namespace App\Filament\Resources\GymProfileResource\Pages;

use App\Filament\Resources\GymProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGymProfiles extends ManageRecords
{
    protected static string $resource = GymProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
