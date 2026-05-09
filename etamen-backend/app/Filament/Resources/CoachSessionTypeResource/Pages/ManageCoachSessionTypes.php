<?php

namespace App\Filament\Resources\CoachSessionTypeResource\Pages;

use App\Filament\Resources\CoachSessionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCoachSessionTypes extends ManageRecords
{
    protected static string $resource = CoachSessionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
