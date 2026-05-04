<?php

namespace App\Filament\Resources\ProviderApprovalRequestResource\Pages;

use App\Filament\Resources\ProviderApprovalRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProviderApprovalRequests extends ManageRecords
{
    protected static string $resource = ProviderApprovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
