<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachPackageResource\Pages;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoachPackageResource extends Resource
{
    protected static ?string $model = CoachPackage::class;

    protected static ?string $navigationGroup = 'Fitness';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\TextInput::make('name_ar')->required()->maxLength(255),
            Forms\Components\TextInput::make('name_en')->maxLength(255),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\TextInput::make('sessions_count')->numeric()->required(),
            Forms\Components\TextInput::make('duration_days')->numeric(),
            Forms\Components\TextInput::make('price')->numeric()->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->label('Coach')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('name_ar')->searchable(),
            Tables\Columns\TextColumn::make('sessions_count')->sortable(),
            Tables\Columns\TextColumn::make('price')->money('EGP')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('provider_id')->relationship('provider', 'name_en'),
            Tables\Filters\TernaryFilter::make('is_active'),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCoachPackages::route('/')];
    }
}
