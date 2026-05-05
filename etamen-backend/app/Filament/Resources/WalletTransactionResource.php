<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletTransactionResource\Pages;
use App\Modules\Wallets\Domain\Enums\WalletTransactionStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Infrastructure\Models\WalletTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WalletTransactionResource extends Resource
{
    protected static ?string $model = WalletTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Wallets';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('wallet_id')->disabled(),
            Forms\Components\TextInput::make('source_type')->disabled(),
            Forms\Components\TextInput::make('source_id')->disabled(),
            Forms\Components\Select::make('type')
                ->options(array_combine(WalletTransactionType::values(), WalletTransactionType::values()))
                ->disabled(),
            Forms\Components\TextInput::make('gross_amount')->disabled(),
            Forms\Components\TextInput::make('commission_amount')->disabled(),
            Forms\Components\TextInput::make('net_amount')->disabled(),
            Forms\Components\Select::make('status')
                ->options(array_combine(WalletTransactionStatus::values(), WalletTransactionStatus::values()))
                ->disabled(),
            Forms\Components\TextInput::make('idempotency_key')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('description')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('wallet_id')->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('gross_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('net_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(array_combine(WalletTransactionType::values(), WalletTransactionType::values())),
                Tables\Filters\SelectFilter::make('status')->options(array_combine(WalletTransactionStatus::values(), WalletTransactionStatus::values())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWalletTransactions::route('/'),
        ];
    }
}
