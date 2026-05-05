<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabTestResource\Pages;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LabTestResource extends Resource
{
    protected static ?string $model = LabTest::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Labs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('provider_id')->required()->numeric(),
            Forms\Components\TextInput::make('name_en')->required()->maxLength(255),
            Forms\Components\TextInput::make('name_ar')->maxLength(255),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\TextInput::make('code')->maxLength(100),
            Forms\Components\TextInput::make('price')->numeric()->required()->minValue(0.01),
            Forms\Components\TextInput::make('sample_type')->maxLength(255),
            Forms\Components\Textarea::make('preparation_instructions_en')->columnSpanFull(),
            Forms\Components\Textarea::make('preparation_instructions_ar')->columnSpanFull(),
            Forms\Components\TextInput::make('result_time_hours')->numeric()->minValue(1),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('provider.name_en')->label('Lab')->searchable(),
                Tables\Columns\TextColumn::make('name_en')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('price')->money('EGP')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLabTests::route('/'),
        ];
    }
}
