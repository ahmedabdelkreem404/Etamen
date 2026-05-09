<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachAvailabilitySlotResource\Pages;
use App\Modules\Fitness\Domain\Enums\CoachAvailabilityStatus;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoachAvailabilitySlotResource extends Resource
{
    protected static ?string $model = CoachAvailabilitySlot::class;

    protected static ?string $navigationGroup = 'Fitness';

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\DateTimePicker::make('starts_at')->required(),
            Forms\Components\DateTimePicker::make('ends_at')->required(),
            Forms\Components\Select::make('status')->options(array_combine(CoachAvailabilityStatus::values(), CoachAvailabilityStatus::values()))->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->label('Coach')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->filters([
            Tables\Filters\SelectFilter::make('provider_id')->relationship('provider', 'name_en'),
            Tables\Filters\SelectFilter::make('status'),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCoachAvailabilitySlots::route('/')];
    }
}
