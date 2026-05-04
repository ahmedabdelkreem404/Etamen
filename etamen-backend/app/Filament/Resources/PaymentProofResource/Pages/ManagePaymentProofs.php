<?php

namespace App\Filament\Resources\PaymentProofResource\Pages;

use App\Filament\Resources\PaymentProofResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePaymentProofs extends ManageRecords
{
    protected static string $resource = PaymentProofResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
