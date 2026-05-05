<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentAttemptResource\Pages;
use App\Modules\Payments\Infrastructure\Models\PaymentAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentAttemptResource extends Resource
{
    protected static ?string $model = PaymentAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Payments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('payment_id')->disabled(),
            Forms\Components\TextInput::make('method_type')->disabled(),
            Forms\Components\TextInput::make('gateway_reference')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\Textarea::make('failure_reason')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('payment_id')->sortable(),
                Tables\Columns\TextColumn::make('method_type')->badge(),
                Tables\Columns\TextColumn::make('gateway_reference')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaymentAttempts::route('/'),
        ];
    }
}
