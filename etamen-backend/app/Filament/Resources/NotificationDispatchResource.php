<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationDispatchResource\Pages;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Domain\Enums\NotificationDispatchStatus;
use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationDispatchResource extends Resource
{
    protected static ?string $model = NotificationDispatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Notifications';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user.email')->disabled(),
            Forms\Components\TextInput::make('channel')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('type')->disabled(),
            Forms\Components\Textarea::make('failure_reason')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('payload')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.email')->searchable(),
            Tables\Columns\TextColumn::make('channel')->badge(),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('type')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('scheduled_for')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('sent_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(array_combine(NotificationDispatchStatus::values(), NotificationDispatchStatus::values())),
            Tables\Filters\SelectFilter::make('channel')->options(array_combine(NotificationChannel::values(), NotificationChannel::values())),
            Tables\Filters\SelectFilter::make('category')->options(array_combine(NotificationCategory::values(), NotificationCategory::values())),
        ])->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNotificationDispatches::route('/')];
    }
}
