<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PharmacyOrderItemResource\Pages;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PharmacyOrderItemResource extends Resource
{
    protected static ?string $model = PharmacyOrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Pharmacy';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_id')->disabled(),
            Forms\Components\TextInput::make('product_id')->disabled(),
            Forms\Components\TextInput::make('product_name')->disabled(),
            Forms\Components\TextInput::make('unit_price')->disabled(),
            Forms\Components\TextInput::make('quantity')->disabled(),
            Forms\Components\TextInput::make('line_total')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable(),
                Tables\Columns\TextColumn::make('product_name')->searchable(),
                Tables\Columns\TextColumn::make('unit_price')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('quantity')->sortable(),
                Tables\Columns\TextColumn::make('line_total')->money('EGP')->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePharmacyOrderItems::route('/'),
        ];
    }
}
