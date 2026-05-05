<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTokenResource\Pages;
use App\Modules\Notifications\Infrastructure\Models\NotificationToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationTokenResource extends Resource
{
    protected static ?string $model = NotificationToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Notifications';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user.email')->disabled(),
            Forms\Components\TextInput::make('provider')->disabled(),
            Forms\Components\TextInput::make('device_type')->disabled(),
            Forms\Components\TextInput::make('device_name')->disabled(),
            Forms\Components\TextInput::make('token_hash')->disabled(),
            Forms\Components\Toggle::make('is_active')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.email')->searchable(),
            Tables\Columns\TextColumn::make('provider')->badge(),
            Tables\Columns\TextColumn::make('device_type')->badge(),
            Tables\Columns\TextColumn::make('device_name'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('last_seen_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNotificationTokens::route('/')];
    }
}
