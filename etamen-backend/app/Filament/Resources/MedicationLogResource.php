<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationLogResource\Pages;
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicationLogResource extends Resource
{
    protected static ?string $model = MedicationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Medications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->disabled(),
            Forms\Components\TextInput::make('reminder.medication_name')->disabled(),
            Forms\Components\DateTimePicker::make('scheduled_for')->disabled(),
            Forms\Components\TextInput::make('action')->disabled(),
            Forms\Components\DateTimePicker::make('taken_at')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('reminder.medication_name')->searchable(),
                Tables\Columns\TextColumn::make('scheduled_for')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('action')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')->options(array_combine(MedicationLogAction::values(), MedicationLogAction::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMedicationLogs::route('/')];
    }
}
