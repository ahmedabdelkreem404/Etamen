<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiologyOrderItemResource\Pages;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RadiologyOrderItemResource extends Resource
{
    protected static ?string $model = RadiologyOrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Radiology';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('radiology_order_id')->disabled(),
            Forms\Components\TextInput::make('radiology_scan_id')->disabled(),
            Forms\Components\TextInput::make('scan_name_ar')->disabled(),
            Forms\Components\TextInput::make('scan_name_en')->disabled(),
            Forms\Components\TextInput::make('unit_price')->disabled(),
            Forms\Components\TextInput::make('quantity')->disabled(),
            Forms\Components\TextInput::make('total_price')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->searchable(),
                Tables\Columns\TextColumn::make('scan_name_ar')->searchable(),
                Tables\Columns\TextColumn::make('scan_name_en')->searchable(),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('total_price')->money('EGP'),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRadiologyOrderItems::route('/'),
        ];
    }
}
