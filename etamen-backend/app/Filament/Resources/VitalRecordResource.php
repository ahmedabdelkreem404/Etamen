<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VitalRecordResource\Pages;
use App\Modules\Health\Domain\Enums\VitalFlag;
use App\Modules\Health\Domain\Enums\VitalType;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VitalRecordResource extends Resource
{
    protected static ?string $model = VitalRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Health';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('vital_type')->disabled(),
            Forms\Components\DateTimePicker::make('measured_at')->disabled(),
            Forms\Components\TextInput::make('value_decimal')->disabled(),
            Forms\Components\TextInput::make('value_secondary_decimal')->disabled(),
            Forms\Components\TextInput::make('unit')->disabled(),
            Forms\Components\TextInput::make('flag')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('vital_type')->badge(),
                Tables\Columns\TextColumn::make('value_decimal'),
                Tables\Columns\TextColumn::make('value_secondary_decimal'),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('flag')->badge(),
                Tables\Columns\TextColumn::make('measured_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vital_type')->options(array_combine(VitalType::values(), VitalType::values())),
                Tables\Filters\SelectFilter::make('flag')->options(array_combine(VitalFlag::values(), VitalFlag::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageVitalRecords::route('/')];
    }
}
