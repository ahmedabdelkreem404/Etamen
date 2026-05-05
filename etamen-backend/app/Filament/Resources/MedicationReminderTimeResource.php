<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationReminderTimeResource\Pages;
use App\Modules\Medications\Infrastructure\Models\MedicationReminderTime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicationReminderTimeResource extends Resource
{
    protected static ?string $model = MedicationReminderTime::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Medications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('reminder.medication_name')->disabled(),
            Forms\Components\TimePicker::make('time_of_day')->disabled(),
            Forms\Components\TextInput::make('label')->disabled(),
            Forms\Components\Toggle::make('is_active')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reminder.patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('reminder.medication_name')->searchable(),
                Tables\Columns\TextColumn::make('time_of_day'),
                Tables\Columns\TextColumn::make('label'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMedicationReminderTimes::route('/')];
    }
}
