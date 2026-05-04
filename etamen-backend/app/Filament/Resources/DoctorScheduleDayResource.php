<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorScheduleDayResource\Pages;
use App\Modules\Appointments\Infrastructure\Models\DoctorScheduleDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorScheduleDayResource extends Resource
{
    protected static ?string $model = DoctorScheduleDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('doctor_schedule_id')->required()->numeric(),
            Forms\Components\TextInput::make('day_of_week')->required()->numeric()->minValue(0)->maxValue(6),
            Forms\Components\TimePicker::make('start_time')->seconds(false)->required(),
            Forms\Components\TimePicker::make('end_time')->seconds(false)->required(),
            Forms\Components\Toggle::make('is_active')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('doctor_schedule_id')->sortable(),
                Tables\Columns\TextColumn::make('day_of_week')->sortable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
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
            'index' => Pages\ManageDoctorScheduleDays::route('/'),
        ];
    }
}
