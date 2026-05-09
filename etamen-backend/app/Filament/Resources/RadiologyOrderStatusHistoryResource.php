<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiologyOrderStatusHistoryResource\Pages;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrderStatusHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RadiologyOrderStatusHistoryResource extends Resource
{
    protected static ?string $model = RadiologyOrderStatusHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Radiology';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('radiology_order_id')->disabled(),
            Forms\Components\TextInput::make('from_status')->disabled(),
            Forms\Components\Select::make('to_status')->options(array_combine(RadiologyOrderStatus::values(), RadiologyOrderStatus::values()))->disabled(),
            Forms\Components\TextInput::make('changed_by')->disabled(),
            Forms\Components\Textarea::make('reason')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->searchable(),
                Tables\Columns\TextColumn::make('from_status')->badge(),
                Tables\Columns\TextColumn::make('to_status')->badge(),
                Tables\Columns\TextColumn::make('actor.email')->label('Actor'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('to_status')->options(array_combine(RadiologyOrderStatus::values(), RadiologyOrderStatus::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRadiologyOrderStatusHistories::route('/'),
        ];
    }
}
