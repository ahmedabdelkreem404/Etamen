<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiologyPreparationInstructionResource\Pages;
use App\Modules\Radiology\Infrastructure\Models\RadiologyPreparationInstruction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RadiologyPreparationInstructionResource extends Resource
{
    protected static ?string $model = RadiologyPreparationInstruction::class;

    protected static ?string $navigationGroup = 'Radiology';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('radiology_scan_category_id')->relationship('category', 'name_ar'),
            Forms\Components\Select::make('radiology_scan_id')->relationship('scan', 'name_ar'),
            Forms\Components\TextInput::make('title_ar')->required()->maxLength(255),
            Forms\Components\TextInput::make('title_en')->maxLength(255),
            Forms\Components\Textarea::make('body_ar')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('body_en')->columnSpanFull(),
            Forms\Components\Textarea::make('warning_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('warning_en')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name_ar')->searchable(),
                Tables\Columns\TextColumn::make('scan.name_ar')->searchable(),
                Tables\Columns\TextColumn::make('title_ar')->searchable(),
                Tables\Columns\TextColumn::make('sort_order')->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('radiology_scan_category_id')->relationship('category', 'name_ar'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRadiologyPreparationInstructions::route('/'),
        ];
    }
}
