<?php

namespace App\Filament\Resources\AiMessageResource\Pages;

use App\Filament\Resources\AiMessageResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAiMessages extends ManageRecords
{
    protected static string $resource = AiMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
