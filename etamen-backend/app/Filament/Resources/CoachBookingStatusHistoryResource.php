<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachBookingStatusHistoryResource\Pages;
use App\Modules\Fitness\Infrastructure\Models\CoachBookingStatusHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoachBookingStatusHistoryResource extends Resource
{
    protected static ?string $model = CoachBookingStatusHistory::class;

    protected static ?string $navigationGroup = 'Fitness';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('coach_booking_id')->relationship('booking', 'booking_number')->disabled(),
            Forms\Components\TextInput::make('from_status')->disabled(),
            Forms\Components\TextInput::make('to_status')->disabled(),
            Forms\Components\Textarea::make('reason')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('booking.booking_number')->label('Booking')->searchable(),
            Tables\Columns\TextColumn::make('from_status')->badge(),
            Tables\Columns\TextColumn::make('to_status')->badge(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\ViewAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCoachBookingStatusHistories::route('/')];
    }
}
