<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HospitalProfileResource\Pages;
use App\Modules\Providers\Infrastructure\Models\HospitalProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HospitalProfileResource extends Resource
{
    protected static ?string $model = HospitalProfile::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\TextInput::make('license_number'),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\Toggle::make('emergency_available'),
            Forms\Components\Toggle::make('has_inpatient'),
            Forms\Components\Toggle::make('has_outpatient'),
            Forms\Components\Toggle::make('has_icu'),
            Forms\Components\Toggle::make('has_ambulance'),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('license_number')->searchable(),
            Tables\Columns\IconColumn::make('emergency_available')->boolean(),
            Tables\Columns\IconColumn::make('has_icu')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHospitalProfiles::route('/'),
        ];
    }
}
