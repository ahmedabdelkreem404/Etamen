<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HealthGoalResource\Pages;
use App\Modules\Health\Domain\Enums\HealthGoalStatus;
use App\Modules\Health\Domain\Enums\HealthGoalType;
use App\Modules\Health\Infrastructure\Models\HealthGoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HealthGoalResource extends Resource
{
    protected static ?string $model = HealthGoal::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Health';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('goal_type')->disabled(),
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\TextInput::make('target_value')->disabled(),
            Forms\Components\TextInput::make('unit')->disabled(),
            Forms\Components\DatePicker::make('target_date')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('goal_type')->badge(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('target_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('goal_type')->options(array_combine(HealthGoalType::values(), HealthGoalType::values())),
                Tables\Filters\SelectFilter::make('status')->options(array_combine(HealthGoalStatus::values(), HealthGoalStatus::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageHealthGoals::route('/')];
    }
}
