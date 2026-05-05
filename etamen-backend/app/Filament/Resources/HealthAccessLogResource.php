<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HealthAccessLogResource\Pages;
use App\Modules\Health\Infrastructure\Models\HealthAccessLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HealthAccessLogResource extends Resource
{
    protected static ?string $model = HealthAccessLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = 'Health';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('actor_id')->disabled(),
            Forms\Components\TextInput::make('action')->disabled(),
            Forms\Components\TextInput::make('target_type')->disabled(),
            Forms\Components\TextInput::make('target_id')->disabled(),
            Forms\Components\TextInput::make('ip_address')->disabled(),
            Forms\Components\Textarea::make('user_agent')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('actor.email')->label('Actor')->searchable(),
                Tables\Columns\TextColumn::make('action')->searchable(),
                Tables\Columns\TextColumn::make('target_type'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageHealthAccessLogs::route('/')];
    }
}
