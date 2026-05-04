<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentReviewResource\Pages;
use App\Modules\Appointments\Infrastructure\Models\AppointmentReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentReviewResource extends Resource
{
    protected static ?string $model = AppointmentReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('appointment_id')->disabled(),
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('doctor_profile_id')->disabled(),
            Forms\Components\TextInput::make('rating')->disabled(),
            Forms\Components\Textarea::make('comment')->disabled()->columnSpanFull(),
            Forms\Components\Toggle::make('is_visible'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('appointment_id')->sortable(),
                Tables\Columns\TextColumn::make('patient.name')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('doctor_profile_id')->sortable(),
                Tables\Columns\TextColumn::make('rating')->sortable(),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAppointmentReviews::route('/'),
        ];
    }
}
