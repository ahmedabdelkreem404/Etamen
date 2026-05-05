<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Infrastructure\Models\NotificationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Notifications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->required()->maxLength(255),
            Forms\Components\Select::make('category')->options(array_combine(NotificationCategory::values(), NotificationCategory::values()))->required(),
            Forms\Components\Select::make('channel')->options(array_combine(NotificationChannel::values(), NotificationChannel::values()))->required(),
            Forms\Components\TextInput::make('title_ar')->required()->maxLength(255),
            Forms\Components\TextInput::make('title_en')->maxLength(255),
            Forms\Components\Textarea::make('body_ar')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('body_en')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active'),
            Forms\Components\KeyValue::make('variables')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('key')->searchable(),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('channel')->badge(),
            Tables\Columns\TextColumn::make('title_ar')->searchable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('category')->options(array_combine(NotificationCategory::values(), NotificationCategory::values())),
        ])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNotificationTemplates::route('/')];
    }
}
