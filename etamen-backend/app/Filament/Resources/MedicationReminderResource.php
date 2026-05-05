<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationReminderResource\Pages;
use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicationReminderResource extends Resource
{
    protected static ?string $model = MedicationReminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Medications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->label('Patient')->disabled(),
            Forms\Components\TextInput::make('medication_name')->disabled(),
            Forms\Components\TextInput::make('dosage')->disabled(),
            Forms\Components\TextInput::make('frequency_type')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\DatePicker::make('start_date')->disabled(),
            Forms\Components\DatePicker::make('end_date')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('medication_name')->searchable(),
                Tables\Columns\TextColumn::make('frequency_type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('refill_enabled')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('frequency_type')->options(array_combine(MedicationFrequencyType::values(), MedicationFrequencyType::values())),
                Tables\Filters\SelectFilter::make('status')->options(array_combine(MedicationReminderStatus::values(), MedicationReminderStatus::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMedicationReminders::route('/')];
    }
}
