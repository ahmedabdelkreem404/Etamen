<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiProviderConfigResource\Pages;
use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use App\Modules\AI\Infrastructure\Models\AiProviderConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiProviderConfigResource extends Resource
{
    protected static ?string $model = AiProviderConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationGroup = 'AI Safety';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('provider')->disabled(),
            Forms\Components\Toggle::make('is_active'),
            Forms\Components\TextInput::make('model')->maxLength(255),
            Forms\Components\Select::make('safety_level')->options(array_combine(AiSafetyLevel::values(), AiSafetyLevel::values())),
            Forms\Components\Placeholder::make('encrypted_config_status')
                ->label('Encrypted config')
                ->content(fn (AiProviderConfig $record): string => is_array($record->encrypted_config) && $record->encrypted_config !== []
                    ? 'Encrypted config exists; values are intentionally hidden.'
                    : 'No encrypted config stored.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('model')->searchable(),
                Tables\Columns\TextColumn::make('safety_level')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAiProviderConfigs::route('/')];
    }
}
