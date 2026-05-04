<?php

namespace App\Filament\Resources\ProviderDocumentResource\Pages;

use App\Filament\Resources\ProviderDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProviderDocuments extends ManageRecords
{
    protected static string $resource = ProviderDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
