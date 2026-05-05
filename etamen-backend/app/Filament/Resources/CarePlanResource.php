<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarePlanResource\Pages;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Domain\Enums\CarePlanType;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarePlanResource extends Resource
{
    protected static ?string $model = CarePlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Care Plans';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->disabled(),
            Forms\Components\TextInput::make('provider.name_en')->disabled(),
            Forms\Components\TextInput::make('plan_type')->disabled(),
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\DatePicker::make('start_date')->disabled(),
            Forms\Components\DatePicker::make('end_date')->disabled(),
            Forms\Components\Textarea::make('description')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('goal_text')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('safety_disclaimer')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('provider.name_en')->label('Provider')->searchable(),
                Tables\Columns\TextColumn::make('plan_type')->badge(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan_type')->options(array_combine(CarePlanType::values(), CarePlanType::values())),
                Tables\Filters\SelectFilter::make('status')->options(array_combine(CarePlanStatus::values(), CarePlanStatus::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCarePlans::route('/')];
    }
}
