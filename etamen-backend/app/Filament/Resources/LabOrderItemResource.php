<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabOrderItemResource\Pages;
use App\Modules\Labs\Infrastructure\Models\LabOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LabOrderItemResource extends Resource
{
    protected static ?string $model = LabOrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Labs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_id')->disabled(),
            Forms\Components\TextInput::make('item_type')->disabled(),
            Forms\Components\TextInput::make('item_name')->disabled(),
            Forms\Components\TextInput::make('unit_price')->disabled(),
            Forms\Components\TextInput::make('quantity')->disabled(),
            Forms\Components\TextInput::make('line_total')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->searchable(),
                Tables\Columns\TextColumn::make('item_type')->badge(),
                Tables\Columns\TextColumn::make('item_name')->searchable(),
                Tables\Columns\TextColumn::make('unit_price')->money('EGP'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('line_total')->money('EGP'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLabOrderItems::route('/'),
        ];
    }
}
