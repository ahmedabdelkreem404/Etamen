<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiUsageLogResource\Pages;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Infrastructure\Models\AiUsageLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiUsageLogResource extends Resource
{
    protected static ?string $model = AiUsageLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'AI Safety';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient.email')->label('Patient')->disabled(),
            Forms\Components\TextInput::make('provider')->disabled(),
            Forms\Components\TextInput::make('model')->disabled(),
            Forms\Components\Toggle::make('success')->disabled(),
            Forms\Components\TextInput::make('error_code')->disabled(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('provider')->badge(),
                Tables\Columns\TextColumn::make('model'),
                Tables\Columns\IconColumn::make('success')->boolean(),
                Tables\Columns\TextColumn::make('total_tokens')->sortable(),
                Tables\Columns\TextColumn::make('latency_ms')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')->options(array_combine(AiProvider::values(), AiProvider::values())),
                Tables\Filters\TernaryFilter::make('success'),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAiUsageLogs::route('/')];
    }
}
