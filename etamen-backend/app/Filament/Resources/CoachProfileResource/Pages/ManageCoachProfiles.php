<?php

namespace App\Filament\Resources\CoachProfileResource\Pages;

use App\Filament\Resources\CoachProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCoachProfiles extends ManageRecords
{
    protected static string $resource = CoachProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
