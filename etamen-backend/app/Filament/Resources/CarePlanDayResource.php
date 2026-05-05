<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarePlanDayResource\Pages;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarePlanDayResource extends Resource
{
    protected static ?string $model = CarePlanDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Care Plans';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('plan.title')->disabled(),
            Forms\Components\TextInput::make('day_number')->disabled(),
            Forms\Components\DatePicker::make('day_date')->disabled(),
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\Textarea::make('instructions')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('plan.title')->searchable(),
            Tables\Columns\TextColumn::make('day_number')->sortable(),
            Tables\Columns\TextColumn::make('day_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCarePlanDays::route('/')];
    }
}
