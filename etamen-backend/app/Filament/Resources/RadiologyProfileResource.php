<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiologyProfileResource\Pages;
use App\Modules\Providers\Infrastructure\Models\RadiologyProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RadiologyProfileResource extends Resource
{
    protected static ?string $model = RadiologyProfile::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\TextInput::make('license_number'),
            Forms\Components\Toggle::make('home_service_enabled'),
            Forms\Components\Toggle::make('report_delivery_enabled'),
            Forms\Components\Toggle::make('dicom_supported'),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('license_number')->searchable(),
            Tables\Columns\IconColumn::make('home_service_enabled')->boolean(),
            Tables\Columns\IconColumn::make('report_delivery_enabled')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRadiologyProfiles::route('/'),
        ];
    }
}
