<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GymBookingResource\Pages;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GymBookingResource extends Resource
{
    protected static ?string $model = GymBooking::class;

    protected static ?string $navigationGroup = 'Fitness';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('booking_number')->disabled(),
            Forms\Components\Select::make('patient_user_id')->relationship('patient', 'email')->disabled(),
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->disabled(),
            Forms\Components\Select::make('membership_plan_id')->relationship('membershipPlan', 'name_ar')->disabled(),
            Forms\Components\Select::make('gym_class_id')->relationship('gymClass', 'name_ar')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('total_amount')->disabled(),
            Forms\Components\Textarea::make('notes')->columnSpanFull()->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('booking_number')->searchable(),
            Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
            Tables\Columns\TextColumn::make('provider.name_en')->label('Gym')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('total_amount')->money('EGP')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('provider_id')->relationship('provider', 'name_en'),
            Tables\Filters\SelectFilter::make('status'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageGymBookings::route('/')];
    }
}
