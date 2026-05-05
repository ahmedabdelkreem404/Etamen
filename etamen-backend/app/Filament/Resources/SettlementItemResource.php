<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettlementItemResource\Pages;
use App\Modules\Wallets\Infrastructure\Models\SettlementItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettlementItemResource extends Resource
{
    protected static ?string $model = SettlementItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'Wallets';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('settlement_id')->disabled(),
            Forms\Components\TextInput::make('wallet_transaction_id')->disabled(),
            Forms\Components\TextInput::make('amount')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('settlement_id')->sortable(),
                Tables\Columns\TextColumn::make('wallet_transaction_id')->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettlementItems::route('/'),
        ];
    }
}
