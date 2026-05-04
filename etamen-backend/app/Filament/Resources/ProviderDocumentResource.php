<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderDocumentResource\Pages;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProviderDocumentResource extends Resource
{
    protected static ?string $model = ProviderDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_id')
                    ->relationship('provider', 'id')
                    ->required(),
                Forms\Components\Select::make('file_id')
                    ->relationship('file', 'id')
                    ->required(),
                Forms\Components\TextInput::make('uploaded_by')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('document_type')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('reviewed_by')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('reviewed_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uploaded_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reviewed_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProviderDocuments::route('/'),
        ];
    }
}
