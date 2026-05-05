<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientSurgeryResource\Pages;
use App\Modules\Health\Infrastructure\Models\PatientSurgery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PatientSurgeryResource extends Resource
{
    protected static ?string $model = PatientSurgery::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Health';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('surgery_name')->disabled(),
            Forms\Components\DatePicker::make('surgery_date')->disabled(),
            Forms\Components\TextInput::make('hospital_name')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('surgery_name')->searchable(),
                Tables\Columns\TextColumn::make('surgery_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('hospital_name')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePatientSurgeries::route('/')];
    }
}
