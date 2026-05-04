<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentSlotResource\Pages;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentSlotResource extends Resource
{
    protected static ?string $model = AppointmentSlot::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('doctor_profile_id')->required()->numeric(),
            Forms\Components\TextInput::make('provider_id')->required()->numeric(),
            Forms\Components\TextInput::make('branch_id')->numeric(),
            Forms\Components\DateTimePicker::make('starts_at')->required(),
            Forms\Components\DateTimePicker::make('ends_at')->required(),
            Forms\Components\Select::make('status')->options(array_combine(AppointmentSlotStatus::values(), AppointmentSlotStatus::values()))->required(),
            Forms\Components\DateTimePicker::make('hold_expires_at'),
            Forms\Components\TextInput::make('generated_from_schedule_id')->numeric(),
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
                Tables\Columns\TextColumn::make('ends_at')->dateTime(),
                Tables\Columns\TextColumn::make('status')->badge()->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(AppointmentSlotStatus::values(), AppointmentSlotStatus::values())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAppointmentSlots::route('/'),
        ];
    }
}
