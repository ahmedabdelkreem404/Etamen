<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarePlanFoodItemResource\Pages;
use App\Modules\CarePlans\Domain\Enums\CarePlanFoodCategory;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanFoodItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarePlanFoodItemResource extends Resource
{
    protected static ?string $model = CarePlanFoodItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Care Plans';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('plan.title')->disabled(),
            Forms\Components\TextInput::make('category')->disabled(),
            Forms\Components\TextInput::make('name')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.title')->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('name')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options(array_combine(CarePlanFoodCategory::values(), CarePlanFoodCategory::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCarePlanFoodItems::route('/')];
    }
}
