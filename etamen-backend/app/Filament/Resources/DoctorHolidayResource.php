<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorHolidayResource\Pages;
use App\Modules\Appointments\Infrastructure\Models\DoctorHoliday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorHolidayResource extends Resource
{
    protected static ?string $model = DoctorHoliday::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('doctor_profile_id')->required()->numeric(),
            Forms\Components\TextInput::make('provider_id')->required()->numeric(),
            Forms\Components\DateTimePicker::make('starts_at')->required(),
            Forms\Components\DateTimePicker::make('ends_at')->required(),
            Forms\Components\Textarea::make('reason')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('doctor_profile_id')->sortable(),
                Tables\Columns\TextColumn::make('provider.name_en')->label('Provider')->searchable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable(),
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
            'index' => Pages\ManageDoctorHolidays::route('/'),
        ];
    }
}
