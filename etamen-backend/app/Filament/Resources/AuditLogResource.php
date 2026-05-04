<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('action')->disabled(),
                Forms\Components\TextInput::make('actor_id')->disabled(),
                Forms\Components\TextInput::make('target_type')->disabled(),
                Forms\Components\TextInput::make('target_id')->disabled(),
                Forms\Components\Textarea::make('metadata')->disabled()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('action')->searchable()->badge(),
                Tables\Columns\TextColumn::make('actor.email')->searchable(),
                Tables\Columns\TextColumn::make('target_type')->toggleable(),
                Tables\Columns\TextColumn::make('target_id')->sortable(),
                Tables\Columns\TextColumn::make('ip_address'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAuditLogs::route('/'),
        ];
    }
}
