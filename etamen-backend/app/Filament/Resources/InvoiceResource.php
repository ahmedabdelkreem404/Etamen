<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Payments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('payment_id')->disabled(),
            Forms\Components\TextInput::make('invoice_number')->disabled(),
            Forms\Components\TextInput::make('gross_amount')->disabled(),
            Forms\Components\TextInput::make('commission_amount')->disabled(),
            Forms\Components\TextInput::make('net_amount')->disabled(),
            Forms\Components\TextInput::make('currency')->disabled(),
            Forms\Components\DateTimePicker::make('issued_at')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable(),
                Tables\Columns\TextColumn::make('payment_id')->sortable(),
                Tables\Columns\TextColumn::make('gross_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('net_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('issued_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
        ];
    }
}
