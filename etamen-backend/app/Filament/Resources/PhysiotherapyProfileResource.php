<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhysiotherapyProfileResource\Pages;
use App\Modules\Providers\Infrastructure\Models\PhysiotherapyProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PhysiotherapyProfileResource extends Resource
{
    protected static ?string $model = PhysiotherapyProfile::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Toggle::make('home_visit_enabled'),
            Forms\Components\Toggle::make('center_visit_enabled'),
            Forms\Components\TextInput::make('session_price')->numeric(),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('home_visit_enabled')->boolean(),
            Tables\Columns\IconColumn::make('center_visit_enabled')->boolean(),
            Tables\Columns\TextColumn::make('session_price')->money('EGP'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePhysiotherapyProfiles::route('/'),
        ];
    }
}
