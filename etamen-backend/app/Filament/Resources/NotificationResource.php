<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Notifications';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user.email')->disabled(),
            Forms\Components\TextInput::make('category')->disabled(),
            Forms\Components\TextInput::make('type')->disabled(),
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\Textarea::make('body')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('data')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.email')->searchable(),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('type')->searchable(),
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('priority')->badge(),
            Tables\Columns\IconColumn::make('read_at')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('category')->options(array_combine(NotificationCategory::values(), NotificationCategory::values())),
        ])->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNotifications::route('/')];
    }
}
