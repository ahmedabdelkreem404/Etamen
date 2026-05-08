<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GymProfileResource\Pages;
use App\Modules\Providers\Infrastructure\Models\GymProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GymProfileResource extends Resource
{
    protected static ?string $model = GymProfile::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Toggle::make('men_allowed'),
            Forms\Components\Toggle::make('women_allowed'),
            Forms\Components\Toggle::make('ladies_only_hours'),
            Forms\Components\Toggle::make('has_classes'),
            Forms\Components\Toggle::make('has_personal_training'),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('men_allowed')->boolean(),
            Tables\Columns\IconColumn::make('women_allowed')->boolean(),
            Tables\Columns\IconColumn::make('has_classes')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGymProfiles::route('/'),
        ];
    }
}
