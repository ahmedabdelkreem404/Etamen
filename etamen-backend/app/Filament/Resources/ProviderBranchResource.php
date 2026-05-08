<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderBranchResource\Pages;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProviderBranchResource extends Resource
{
    protected static ?string $model = ProviderBranch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_id')
                    ->relationship('provider', 'id')
                    ->required(),
                Forms\Components\Select::make('city_id')
                    ->relationship('city', 'id'),
                Forms\Components\Select::make('area_id')
                    ->relationship('area', 'id'),
                Forms\Components\TextInput::make('name_ar'),
                Forms\Components\TextInput::make('name_en')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel(),
                Forms\Components\TextInput::make('whatsapp')
                    ->tel(),
                Forms\Components\TextInput::make('address_line_1'),
                Forms\Components\TextInput::make('address_line_2'),
                Forms\Components\TextInput::make('district'),
                Forms\Components\Textarea::make('address_ar')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('address_en')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->numeric(),
                Forms\Components\Textarea::make('working_hours_json')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_24_hours'),
                Forms\Components\TextInput::make('home_service_radius_km')
                    ->numeric(),
                Forms\Components\TextInput::make('delivery_radius_km')
                    ->numeric(),
                Forms\Components\Toggle::make('is_main')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('area.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_24_hours')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_main')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProviderBranches::route('/'),
        ];
    }
}
