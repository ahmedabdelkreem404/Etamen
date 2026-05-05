<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletStatus;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Wallets';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('owner_type')
                ->options(array_combine(WalletOwnerType::values(), WalletOwnerType::values()))
                ->disabled(),
            Forms\Components\TextInput::make('owner_id')->disabled(),
            Forms\Components\TextInput::make('currency')->disabled(),
            Forms\Components\Select::make('status')
                ->options(array_combine(WalletStatus::values(), WalletStatus::values()))
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('owner_type')->badge(),
                Tables\Columns\TextColumn::make('owner_id')->sortable(),
                Tables\Columns\TextColumn::make('currency')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('owner_type')->options(array_combine(WalletOwnerType::values(), WalletOwnerType::values())),
                Tables\Filters\SelectFilter::make('status')->options(array_combine(WalletStatus::values(), WalletStatus::values())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWallets::route('/'),
        ];
    }
}
