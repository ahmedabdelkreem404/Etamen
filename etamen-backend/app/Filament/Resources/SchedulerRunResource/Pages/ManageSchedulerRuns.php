<?php

namespace App\Filament\Resources\SchedulerRunResource\Pages;

use App\Filament\Resources\SchedulerRunResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSchedulerRuns extends ManageRecords
{
    protected static string $resource = SchedulerRunResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
