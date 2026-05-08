<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeHealthcareProfileResource\Pages;
use App\Modules\Providers\Infrastructure\Models\HomeHealthcareProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeHealthcareProfileResource extends Resource
{
    protected static ?string $model = HomeHealthcareProfile::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Toggle::make('nursing_enabled'),
            Forms\Components\Toggle::make('injections_enabled'),
            Forms\Components\Toggle::make('wound_care_enabled'),
            Forms\Components\Toggle::make('elderly_care_enabled'),
            Forms\Components\Toggle::make('physiotherapy_home_enabled'),
            Forms\Components\TextInput::make('service_radius_km')->numeric(),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('nursing_enabled')->boolean(),
            Tables\Columns\IconColumn::make('wound_care_enabled')->boolean(),
            Tables\Columns\IconColumn::make('elderly_care_enabled')->boolean(),
            Tables\Columns\TextColumn::make('service_radius_km')->numeric(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHomeHealthcareProfiles::route('/'),
        ];
    }
}
