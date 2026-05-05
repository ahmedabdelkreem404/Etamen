<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionRuleResource\Pages;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Wallets\Infrastructure\Models\CommissionRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CommissionRuleResource extends Resource
{
    protected static ?string $model = CommissionRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Wallets';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_type')
                ->options(array_combine(ProviderType::values(), ProviderType::values()))
                ->required(),
            Forms\Components\Select::make('service_type')
                ->options(array_combine(ServiceType::values(), ServiceType::values()))
                ->required(),
            Forms\Components\TextInput::make('percentage')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->required(),
            Forms\Components\TextInput::make('fixed_amount')
                ->numeric()
                ->minValue(0),
            Forms\Components\DateTimePicker::make('starts_at')->required(),
            Forms\Components\DateTimePicker::make('ends_at'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider_type')->badge(),
                Tables\Columns\TextColumn::make('service_type')->badge(),
                Tables\Columns\TextColumn::make('percentage')->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('fixed_amount')->money('EGP')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider_type')->options(array_combine(ProviderType::values(), ProviderType::values())),
                Tables\Filters\SelectFilter::make('service_type')->options(array_combine(ServiceType::values(), ServiceType::values())),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCommissionRules::route('/'),
        ];
    }
}
