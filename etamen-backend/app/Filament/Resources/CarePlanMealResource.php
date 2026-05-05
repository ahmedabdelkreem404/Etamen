<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarePlanMealResource\Pages;
use App\Modules\CarePlans\Domain\Enums\CarePlanMealType;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanMeal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarePlanMealResource extends Resource
{
    protected static ?string $model = CarePlanMeal::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Care Plans';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('day.plan.title')->disabled(),
            Forms\Components\TextInput::make('meal_type')->disabled(),
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\Textarea::make('description')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('instructions')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day.plan.title')->label('Plan')->searchable(),
                Tables\Columns\TextColumn::make('meal_type')->badge(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('calories'),
                Tables\Columns\IconColumn::make('is_required')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('meal_type')->options(array_combine(CarePlanMealType::values(), CarePlanMealType::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCarePlanMeals::route('/')];
    }
}
