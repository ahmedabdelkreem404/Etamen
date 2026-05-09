<?php

namespace App\Filament\Resources\GymMembershipPlanResource\Pages;

use App\Filament\Resources\GymMembershipPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGymMembershipPlans extends ManageRecords
{
    protected static string $resource = GymMembershipPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
