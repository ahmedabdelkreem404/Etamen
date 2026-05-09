<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachSessionTypeResource\Pages;
use App\Modules\Fitness\Domain\Enums\CoachSessionMode;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoachSessionTypeResource extends Resource
{
    protected static ?string $model = CoachSessionType::class;

    protected static ?string $navigationGroup = 'Fitness';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\TextInput::make('name_ar')->required()->maxLength(255),
            Forms\Components\TextInput::make('name_en')->maxLength(255),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\TextInput::make('duration_minutes')->numeric()->required(),
            Forms\Components\TextInput::make('price')->numeric()->required(),
            Forms\Components\Select::make('session_mode')->options(array_combine(CoachSessionMode::values(), CoachSessionMode::values()))->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->label('Coach')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('name_ar')->searchable(),
            Tables\Columns\TextColumn::make('session_mode')->badge(),
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
        return ['index' => Pages\ManageCoachSessionTypes::route('/')];
    }
}
