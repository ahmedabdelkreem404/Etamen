<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarePlanCheckinResource\Pages;
use App\Modules\CarePlans\Domain\Enums\CarePlanMood;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanCheckin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarePlanCheckinResource extends Resource
{
    protected static ?string $model = CarePlanCheckin::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Care Plans';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->disabled(),
            Forms\Components\TextInput::make('plan.title')->disabled(),
            Forms\Components\DatePicker::make('checkin_date')->disabled(),
            Forms\Components\TextInput::make('commitment_score')->disabled(),
            Forms\Components\TextInput::make('mood')->disabled(),
            Forms\Components\Textarea::make('symptoms_notes')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('general_notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('plan.title')->searchable(),
                Tables\Columns\TextColumn::make('checkin_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('commitment_score')->sortable(),
                Tables\Columns\TextColumn::make('mood')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mood')->options(array_combine(CarePlanMood::values(), CarePlanMood::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCarePlanCheckins::route('/')];
    }
}
