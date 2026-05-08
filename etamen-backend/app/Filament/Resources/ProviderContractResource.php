<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderContractResource\Pages;
use App\Modules\Providers\Domain\Enums\ProviderContractStatus;
use App\Modules\Providers\Domain\Enums\ProviderContractType;
use App\Modules\Providers\Domain\Enums\ProviderSettlementCycle;
use App\Modules\Providers\Infrastructure\Models\ProviderContract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProviderContractResource extends Resource
{
    protected static ?string $model = ProviderContract::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Select::make('contract_type')->options(array_combine(ProviderContractType::values(), ProviderContractType::values()))->required(),
            Forms\Components\TextInput::make('commission_rate')->numeric(),
            Forms\Components\TextInput::make('fixed_commission_amount')->numeric(),
            Forms\Components\TextInput::make('subscription_plan_id')->numeric(),
            Forms\Components\Select::make('settlement_cycle')->options(array_combine(ProviderSettlementCycle::values(), ProviderSettlementCycle::values()))->required(),
            Forms\Components\Toggle::make('pay_at_branch_allowed'),
            Forms\Components\Toggle::make('online_payment_required'),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('ends_at'),
            Forms\Components\Select::make('status')->options(array_combine(ProviderContractStatus::values(), ProviderContractStatus::values()))->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('contract_type')->searchable(),
            Tables\Columns\TextColumn::make('settlement_cycle')->searchable(),
            Tables\Columns\IconColumn::make('pay_at_branch_allowed')->boolean(),
            Tables\Columns\IconColumn::make('online_payment_required')->boolean(),
            Tables\Columns\TextColumn::make('status')->searchable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProviderContracts::route('/'),
        ];
    }
}
