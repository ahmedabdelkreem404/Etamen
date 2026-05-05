<?php

namespace App\Filament\Resources\AiProviderConfigResource\Pages;

use App\Filament\Resources\AiProviderConfigResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAiProviderConfigs extends ManageRecords
{
    protected static string $resource = AiProviderConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
