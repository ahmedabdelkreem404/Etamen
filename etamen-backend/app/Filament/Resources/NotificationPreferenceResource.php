<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationPreferenceResource\Pages;
use App\Modules\Notifications\Infrastructure\Models\NotificationPreference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationPreferenceResource extends Resource
{
    protected static ?string $model = NotificationPreference::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Notifications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user.email')->disabled(),
            Forms\Components\TextInput::make('channel')->disabled(),
            Forms\Components\TextInput::make('category')->disabled(),
            Forms\Components\Toggle::make('is_enabled')->disabled(),
            Forms\Components\TextInput::make('quiet_hours_start')->disabled(),
            Forms\Components\TextInput::make('quiet_hours_end')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.email')->searchable(),
            Tables\Columns\TextColumn::make('channel')->badge(),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\IconColumn::make('is_enabled')->boolean(),
            Tables\Columns\TextColumn::make('timezone'),
        ])->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNotificationPreferences::route('/')];
    }
}
