<?php

namespace App\Filament\Resources\AiUsageLogResource\Pages;

use App\Filament\Resources\AiUsageLogResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAiUsageLogs extends ManageRecords
{
    protected static string $resource = AiUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
