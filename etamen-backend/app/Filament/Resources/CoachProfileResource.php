<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachProfileResource\Pages;
use App\Modules\Providers\Domain\Enums\CoachType;
use App\Modules\Providers\Infrastructure\Models\CoachProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoachProfileResource extends Resource
{
    protected static ?string $model = CoachProfile::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Select::make('coach_type')->options(array_combine(CoachType::values(), CoachType::values()))->required(),
            Forms\Components\TextInput::make('experience_years')->numeric(),
            Forms\Components\TextInput::make('session_price')->numeric(),
            Forms\Components\TextInput::make('monthly_followup_price')->numeric(),
            Forms\Components\Toggle::make('online_coaching_enabled'),
            Forms\Components\Toggle::make('gym_visit_enabled'),
            Forms\Components\Toggle::make('home_training_enabled'),
            Forms\Components\Textarea::make('certifications_summary')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('coach_type')->searchable(),
            Tables\Columns\TextColumn::make('experience_years')->numeric(),
            Tables\Columns\TextColumn::make('session_price')->money('EGP'),
            Tables\Columns\IconColumn::make('online_coaching_enabled')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCoachProfiles::route('/'),
        ];
    }
}
