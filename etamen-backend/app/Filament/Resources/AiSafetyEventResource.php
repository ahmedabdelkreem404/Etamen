<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiSafetyEventResource\Pages;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiSafetyEventResource extends Resource
{
    protected static ?string $model = AiSafetyEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'AI Safety';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->label('Patient')->disabled(),
            Forms\Components\TextInput::make('event_type')->disabled(),
            Forms\Components\TextInput::make('severity')->disabled(),
            Forms\Components\Textarea::make('description')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('event_type')->badge(),
                Tables\Columns\TextColumn::make('severity')->badge(),
                Tables\Columns\TextColumn::make('description')->limit(70),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')->options(array_combine(AiSafetyEventType::values(), AiSafetyEventType::values())),
                Tables\Filters\SelectFilter::make('severity')->options(array_combine(AiSafetySeverity::values(), AiSafetySeverity::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAiSafetyEvents::route('/')];
    }
}
