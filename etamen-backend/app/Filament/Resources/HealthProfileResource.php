<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HealthProfileResource\Pages;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HealthProfileResource extends Resource
{
    protected static ?string $model = HealthProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Health';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\DatePicker::make('date_of_birth')->disabled(),
            Forms\Components\TextInput::make('gender')->disabled(),
            Forms\Components\TextInput::make('height_cm')->disabled(),
            Forms\Components\TextInput::make('weight_kg')->disabled(),
            Forms\Components\TextInput::make('blood_type')->disabled(),
            Forms\Components\TextInput::make('emergency_contact_name')->disabled(),
            Forms\Components\TextInput::make('emergency_contact_phone')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('gender')->badge(),
                Tables\Columns\TextColumn::make('blood_type')->badge(),
                Tables\Columns\TextColumn::make('height_cm'),
                Tables\Columns\TextColumn::make('weight_kg'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageHealthProfiles::route('/')];
    }
}
