<?php

namespace App\Filament\Resources\AiSafetyEventResource\Pages;

use App\Filament\Resources\AiSafetyEventResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAiSafetyEvents extends ManageRecords
{
    protected static string $resource = AiSafetyEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
