<?php

namespace App\Filament\Resources\CoachAvailabilitySlotResource\Pages;

use App\Filament\Resources\CoachAvailabilitySlotResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCoachAvailabilitySlots extends ManageRecords
{
    protected static string $resource = CoachAvailabilitySlotResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
