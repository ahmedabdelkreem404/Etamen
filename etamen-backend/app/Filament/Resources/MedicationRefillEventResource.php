<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationRefillEventResource\Pages;
use App\Modules\Medications\Domain\Enums\MedicationRefillEventType;
use App\Modules\Medications\Infrastructure\Models\MedicationRefillEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicationRefillEventResource extends Resource
{
    protected static ?string $model = MedicationRefillEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Medications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->disabled(),
            Forms\Components\TextInput::make('reminder.medication_name')->disabled(),
            Forms\Components\TextInput::make('event_type')->disabled(),
            Forms\Components\DatePicker::make('event_date')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('reminder.medication_name')->searchable(),
                Tables\Columns\TextColumn::make('event_type')->badge(),
                Tables\Columns\TextColumn::make('event_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')->options(array_combine(MedicationRefillEventType::values(), MedicationRefillEventType::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMedicationRefillEvents::route('/')];
    }
}
