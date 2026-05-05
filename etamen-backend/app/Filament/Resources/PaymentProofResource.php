<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentProofResource\Pages;
use App\Modules\Payments\Infrastructure\Models\PaymentProof;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentProofResource extends Resource
{
    protected static ?string $model = PaymentProof::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payment_id')->disabled(),
                Forms\Components\TextInput::make('reference_number')->disabled(),
                Forms\Components\TextInput::make('sender_phone')->disabled(),
                Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
                Forms\Components\TextInput::make('status')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('payment_id')->sortable(),
                Tables\Columns\TextColumn::make('uploader.email')->searchable(),
                Tables\Columns\TextColumn::make('file.original_name')->label('File'),
                Tables\Columns\TextColumn::make('file.visibility')->label('Visibility'),
                Tables\Columns\TextColumn::make('reference_number')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => $state?->value ?? (string) $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'uploaded' => 'uploaded',
                        'pending_review' => 'pending_review',
                        'accepted' => 'accepted',
                        'rejected' => 'rejected',
                    ]),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaymentProofs::route('/'),
        ];
    }
}
