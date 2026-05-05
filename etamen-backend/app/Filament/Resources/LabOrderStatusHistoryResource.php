<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabOrderStatusHistoryResource\Pages;
use App\Modules\Labs\Infrastructure\Models\LabOrderStatusHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LabOrderStatusHistoryResource extends Resource
{
    protected static ?string $model = LabOrderStatusHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Labs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_id')->disabled(),
            Forms\Components\TextInput::make('from_status')->disabled(),
            Forms\Components\TextInput::make('to_status')->disabled(),
            Forms\Components\TextInput::make('actor_id')->disabled(),
            Forms\Components\Textarea::make('reason')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->searchable(),
                Tables\Columns\TextColumn::make('from_status')->badge(),
                Tables\Columns\TextColumn::make('to_status')->badge(),
                Tables\Columns\TextColumn::make('actor_id'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLabOrderStatusHistories::route('/'),
        ];
    }
}
