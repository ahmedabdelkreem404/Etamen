<?php

namespace App\Filament\Resources\AiConversationResource\Pages;

use App\Filament\Resources\AiConversationResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAiConversations extends ManageRecords
{
    protected static string $resource = AiConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
