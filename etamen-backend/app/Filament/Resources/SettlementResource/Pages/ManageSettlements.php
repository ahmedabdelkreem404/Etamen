<?php

namespace App\Filament\Resources\SettlementResource\Pages;

use App\Filament\Resources\SettlementResource;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Wallets\Application\Services\SettlementService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ManageRecords;

class ManageSettlements extends ManageRecords
{
    protected static string $resource = SettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_settlement')
                ->form([
                    Forms\Components\TextInput::make('provider_id')->required()->numeric(),
                    Forms\Components\Select::make('provider_type')
                        ->options(array_combine(ProviderType::values(), ProviderType::values()))
                        ->default(ProviderType::Doctor->value)
                        ->required(),
                ])
                ->action(fn (array $data) => app(SettlementService::class)->create(
                    auth()->user(),
                    (int) $data['provider_id'],
                    ProviderType::from($data['provider_type']),
                )),
        ];
    }
}
