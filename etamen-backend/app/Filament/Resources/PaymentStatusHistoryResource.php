<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentStatusHistoryResource\Pages;
use App\Modules\Payments\Infrastructure\Models\PaymentStatusHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentStatusHistoryResource extends Resource
{
    protected static ?string $model = PaymentStatusHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Payments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('payment_id')->disabled(),
            Forms\Components\TextInput::make('from_status')->disabled(),
            Forms\Components\TextInput::make('to_status')->disabled(),
            Forms\Components\TextInput::make('actor_id')->disabled(),
            Forms\Components\Textarea::make('reason')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('payment_id')->sortable(),
                Tables\Columns\TextColumn::make('from_status')->badge(),
                Tables\Columns\TextColumn::make('to_status')->badge()->searchable(),
                Tables\Columns\TextColumn::make('actor.email')->label('Actor')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaymentStatusHistories::route('/'),
        ];
    }
}
