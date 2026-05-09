<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GymMembershipPlanResource\Pages;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GymMembershipPlanResource extends Resource
{
    protected static ?string $model = GymMembershipPlan::class;

    protected static ?string $navigationGroup = 'Fitness';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Select::make('branch_id')->relationship('branch', 'name_en'),
            Forms\Components\TextInput::make('name_ar')->required()->maxLength(255),
            Forms\Components\TextInput::make('name_en')->maxLength(255),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\TextInput::make('duration_days')->numeric()->required(),
            Forms\Components\TextInput::make('price')->numeric()->required(),
            Forms\Components\TextInput::make('sessions_count')->numeric(),
            Forms\Components\Toggle::make('includes_classes'),
            Forms\Components\Toggle::make('includes_personal_training'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->label('Provider')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('name_ar')->searchable(),
            Tables\Columns\TextColumn::make('duration_days')->sortable(),
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
        return ['index' => Pages\ManageGymMembershipPlans::route('/')];
    }
}
