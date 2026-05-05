<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiConversationResource\Pages;
use App\Modules\AI\Domain\Enums\AiConversationStatus;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiConversationResource extends Resource
{
    protected static ?string $model = AiConversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'AI Safety';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->label('Patient')->disabled(),
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('provider')->disabled(),
            Forms\Components\TextInput::make('language')->disabled(),
            Forms\Components\Toggle::make('context_enabled')->disabled(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('provider')->badge(),
                Tables\Columns\IconColumn::make('context_enabled')->boolean(),
                Tables\Columns\TextColumn::make('last_message_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(AiConversationStatus::values(), AiConversationStatus::values())),
                Tables\Filters\SelectFilter::make('provider')->options(array_combine(AiProvider::values(), AiProvider::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAiConversations::route('/')];
    }
}
