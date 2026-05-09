<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiologyResultResource\Pages;
use App\Modules\Radiology\Domain\Enums\RadiologyResultType;
use App\Modules\Radiology\Infrastructure\Models\RadiologyResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RadiologyResultResource extends Resource
{
    protected static ?string $model = RadiologyResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'Radiology';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('radiology_order_id')->disabled(),
            Forms\Components\TextInput::make('uploaded_file_id')->disabled(),
            Forms\Components\TextInput::make('uploaded_by')->disabled(),
            Forms\Components\Select::make('result_type')->options(array_combine(RadiologyResultType::values(), RadiologyResultType::values()))->required(),
            Forms\Components\TextInput::make('title_ar')->maxLength(255),
            Forms\Components\TextInput::make('title_en')->maxLength(255),
            Forms\Components\Toggle::make('is_visible_to_patient'),
            Forms\Components\Textarea::make('notes_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('notes_en')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->searchable(),
                Tables\Columns\TextColumn::make('result_type')->badge(),
                Tables\Columns\IconColumn::make('is_visible_to_patient')->boolean(),
                Tables\Columns\TextColumn::make('file.original_name')->label('File'),
                Tables\Columns\TextColumn::make('uploaded_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('result_type')->options(array_combine(RadiologyResultType::values(), RadiologyResultType::values())),
                Tables\Filters\TernaryFilter::make('is_visible_to_patient'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRadiologyResults::route('/'),
        ];
    }
}
