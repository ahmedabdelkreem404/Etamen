<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientAllergyResource\Pages;
use App\Modules\Health\Domain\Enums\AllergySeverity;
use App\Modules\Health\Infrastructure\Models\PatientAllergy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PatientAllergyResource extends Resource
{
    protected static ?string $model = PatientAllergy::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Health';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('allergen')->disabled(),
            Forms\Components\TextInput::make('reaction')->disabled(),
            Forms\Components\TextInput::make('severity')->disabled(),
            Forms\Components\Toggle::make('is_active')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('allergen')->searchable(),
                Tables\Columns\TextColumn::make('severity')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')->options(array_combine(AllergySeverity::values(), AllergySeverity::values())),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePatientAllergies::route('/')];
    }
}
