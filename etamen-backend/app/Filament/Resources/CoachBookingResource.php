<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachBookingResource\Pages;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoachBookingResource extends Resource
{
    protected static ?string $model = CoachBooking::class;

    protected static ?string $navigationGroup = 'Fitness';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('booking_number')->disabled(),
            Forms\Components\Select::make('patient_user_id')->relationship('patient', 'email')->disabled(),
            Forms\Components\Select::make('coach_provider_id')->relationship('coachProvider', 'name_en')->disabled(),
            Forms\Components\Select::make('session_type_id')->relationship('sessionType', 'name_ar')->disabled(),
            Forms\Components\Select::make('availability_slot_id')->relationship('availabilitySlot', 'starts_at')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('total_amount')->disabled(),
            Forms\Components\Textarea::make('patient_goal')->columnSpanFull()->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('booking_number')->searchable(),
            Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
            Tables\Columns\TextColumn::make('coachProvider.name_en')->label('Coach')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('total_amount')->money('EGP')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('coach_provider_id')->relationship('coachProvider', 'name_en'),
            Tables\Filters\SelectFilter::make('status'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCoachBookings::route('/')];
    }
}
