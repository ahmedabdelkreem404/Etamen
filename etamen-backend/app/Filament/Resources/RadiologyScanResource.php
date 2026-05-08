<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiologyScanResource\Pages;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RadiologyScanResource extends Resource
{
    protected static ?string $model = RadiologyScan::class;

    protected static ?string $navigationGroup = 'Radiology';

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Select::make('branch_id')->relationship('branch', 'name_en'),
            Forms\Components\Select::make('radiology_scan_category_id')->relationship('category', 'name_ar')->required(),
            Forms\Components\TextInput::make('name_ar')->required()->maxLength(255),
            Forms\Components\TextInput::make('name_en')->maxLength(255),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\Textarea::make('preparation_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('preparation_en')->columnSpanFull(),
            Forms\Components\TextInput::make('duration_minutes')->numeric(),
            Forms\Components\TextInput::make('base_price')->numeric(),
            Forms\Components\Toggle::make('requires_preparation'),
            Forms\Components\Toggle::make('requires_fasting'),
            Forms\Components\Toggle::make('contrast_required'),
            Forms\Components\Toggle::make('home_available'),
            Forms\Components\Toggle::make('branch_available')->default(true),
            Forms\Components\Toggle::make('report_delivery_enabled')->default(true),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider.name_en')->label('Provider')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name_ar')->label('Category')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name_ar')->searchable(),
                Tables\Columns\TextColumn::make('name_en')->searchable(),
                Tables\Columns\TextColumn::make('base_price')->money('EGP')->sortable(),
                Tables\Columns\IconColumn::make('requires_preparation')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('radiology_scan_category_id')->relationship('category', 'name_ar'),
                Tables\Filters\SelectFilter::make('provider_id')->relationship('provider', 'name_en'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRadiologyScans::route('/'),
        ];
    }
}
