<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorScheduleResource\Pages;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorScheduleResource extends Resource
{
    protected static ?string $model = DoctorSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('doctor_profile_id')->required()->numeric(),
            Forms\Components\TextInput::make('provider_id')->required()->numeric(),
            Forms\Components\TextInput::make('branch_id')->numeric(),
            Forms\Components\TextInput::make('name')->maxLength(255),
            Forms\Components\Toggle::make('is_active')->required(),
            Forms\Components\TextInput::make('slot_duration_minutes')->required()->numeric()->minValue(5),
            Forms\Components\TextInput::make('buffer_minutes')->required()->numeric()->minValue(0),
            Forms\Components\TextInput::make('max_days_ahead')->required()->numeric()->minValue(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('doctor_profile_id')->sortable(),
                Tables\Columns\TextColumn::make('provider.name_en')->label('Provider')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('slot_duration_minutes')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDoctorSchedules::route('/'),
        ];
    }
}
