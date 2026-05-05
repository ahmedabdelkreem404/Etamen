<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientChronicDiseaseResource\Pages;
use App\Modules\Health\Infrastructure\Models\PatientChronicDisease;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PatientChronicDiseaseResource extends Resource
{
    protected static ?string $model = PatientChronicDisease::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Health';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('name')->disabled(),
            Forms\Components\DatePicker::make('diagnosed_at')->disabled(),
            Forms\Components\Toggle::make('is_active')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('diagnosed_at')->date(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePatientChronicDiseases::route('/')];
    }
}
