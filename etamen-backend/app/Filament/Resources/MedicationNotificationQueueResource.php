<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationNotificationQueueResource\Pages;
use App\Modules\Medications\Domain\Enums\MedicationNotificationStatus;
use App\Modules\Medications\Domain\Enums\MedicationNotificationType;
use App\Modules\Medications\Infrastructure\Models\MedicationNotificationQueue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicationNotificationQueueResource extends Resource
{
    protected static ?string $model = MedicationNotificationQueue::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Medications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->disabled(),
            Forms\Components\TextInput::make('reminder.medication_name')->disabled(),
            Forms\Components\DateTimePicker::make('scheduled_for')->disabled(),
            Forms\Components\TextInput::make('notification_type')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('channel')->disabled(),
            Forms\Components\KeyValue::make('payload')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('failure_reason')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('reminder.medication_name')->searchable(),
                Tables\Columns\TextColumn::make('scheduled_for')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('notification_type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('channel')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('notification_type')->options(array_combine(MedicationNotificationType::values(), MedicationNotificationType::values())),
                Tables\Filters\SelectFilter::make('status')->options(array_combine(MedicationNotificationStatus::values(), MedicationNotificationStatus::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMedicationNotificationQueue::route('/')];
    }
}
