<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientCurrentMedicationResource\Pages;
use App\Modules\Health\Infrastructure\Models\PatientCurrentMedication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PatientCurrentMedicationResource extends Resource
{
    protected static ?string $model = PatientCurrentMedication::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Health';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('medication_name')->disabled(),
            Forms\Components\TextInput::make('dosage')->disabled(),
            Forms\Components\TextInput::make('frequency_text')->disabled(),
            Forms\Components\TextInput::make('prescribed_by')->disabled(),
            Forms\Components\Toggle::make('is_active')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('medication_name')->searchable(),
                Tables\Columns\TextColumn::make('dosage'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePatientCurrentMedications::route('/')];
    }
}
