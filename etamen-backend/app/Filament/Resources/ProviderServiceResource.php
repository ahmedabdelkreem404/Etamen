<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderServiceResource\Pages;
use App\Modules\Providers\Infrastructure\Models\ProviderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProviderServiceResource extends Resource
{
    protected static ?string $model = ProviderService::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Select::make('branch_id')->relationship('branch', 'name_en'),
            Forms\Components\Select::make('service_category_id')->relationship('category', 'name_ar'),
            Forms\Components\TextInput::make('service_type')->required(),
            Forms\Components\TextInput::make('name_ar')->required(),
            Forms\Components\TextInput::make('name_en'),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\TextInput::make('duration_minutes')->numeric(),
            Forms\Components\TextInput::make('base_price')->numeric(),
            Forms\Components\Toggle::make('online_available'),
            Forms\Components\Toggle::make('home_available'),
            Forms\Components\Toggle::make('branch_available'),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('service_type')->searchable(),
            Tables\Columns\TextColumn::make('name_ar')->searchable(),
            Tables\Columns\TextColumn::make('base_price')->money('EGP'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProviderServices::route('/'),
        ];
    }
}
