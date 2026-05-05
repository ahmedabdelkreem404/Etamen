<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiMessageResource\Pages;
use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiMessageResource extends Resource
{
    protected static ?string $model = AiMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'AI Safety';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->label('Patient')->disabled(),
            Forms\Components\TextInput::make('role')->disabled(),
            Forms\Components\TextInput::make('safety_classification')->disabled(),
            Forms\Components\Toggle::make('was_refused')->disabled(),
            Forms\Components\Textarea::make('content')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('role')->badge(),
                Tables\Columns\TextColumn::make('safety_classification')->badge(),
                Tables\Columns\IconColumn::make('was_refused')->boolean(),
                Tables\Columns\TextColumn::make('provider')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('safety_classification')->options(array_combine(AiSafetyClassification::values(), AiSafetyClassification::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAiMessages::route('/')];
    }
}
