<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UploadedFileResource\Pages;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UploadedFileResource extends Resource
{
    protected static ?string $model = UploadedFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Medical Files';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('original_name')->disabled(),
                Forms\Components\TextInput::make('disk')->disabled(),
                Forms\Components\TextInput::make('path')->disabled()->columnSpanFull(),
                Forms\Components\TextInput::make('mime_type')->disabled(),
                Forms\Components\TextInput::make('size')->disabled(),
                Forms\Components\TextInput::make('file_category')->disabled(),
                Forms\Components\TextInput::make('visibility')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('file_category')
                    ->label('Category')
                    ->formatStateUsing(fn ($state): string => $state?->value ?? (string) $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('original_name')->searchable(),
                Tables\Columns\TextColumn::make('mime_type'),
                Tables\Columns\TextColumn::make('visibility')
                    ->formatStateUsing(fn ($state): string => $state?->value ?? (string) $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ManageUploadedFiles::route('/'),
        ];
    }
}
