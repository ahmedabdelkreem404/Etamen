<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealLogResource\Pages;
use App\Modules\CarePlans\Domain\Enums\MealLogStatus;
use App\Modules\CarePlans\Infrastructure\Models\MealLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MealLogResource extends Resource
{
    protected static ?string $model = MealLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Care Plans';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->disabled(),
            Forms\Components\TextInput::make('plan.title')->disabled(),
            Forms\Components\DateTimePicker::make('logged_at')->disabled(),
            Forms\Components\TextInput::make('meal_type')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\Textarea::make('description')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('plan.title')->searchable(),
                Tables\Columns\TextColumn::make('logged_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('meal_type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(MealLogStatus::values(), MealLogStatus::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMealLogs::route('/')];
    }
}
