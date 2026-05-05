<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettlementResource\Pages;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Wallets\Application\Services\SettlementService;
use App\Modules\Wallets\Domain\Enums\SettlementStatus;
use App\Modules\Wallets\Infrastructure\Models\Settlement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettlementResource extends Resource
{
    protected static ?string $model = Settlement::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Wallets';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('provider_id')->required()->numeric(),
            Forms\Components\Select::make('provider_type')
                ->options(array_combine(ProviderType::values(), ProviderType::values()))
                ->default(ProviderType::Doctor->value)
                ->required(),
            Forms\Components\TextInput::make('total_gross')->disabled(),
            Forms\Components\TextInput::make('total_commission')->disabled(),
            Forms\Components\TextInput::make('total_net')->disabled(),
            Forms\Components\Select::make('status')
                ->options(array_combine(SettlementStatus::values(), SettlementStatus::values()))
                ->disabled(),
            Forms\Components\TextInput::make('settled_by')->disabled(),
            Forms\Components\DateTimePicker::make('settled_at')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('provider_id')->sortable(),
                Tables\Columns\TextColumn::make('provider_type')->badge(),
                Tables\Columns\TextColumn::make('total_gross')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('total_commission')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('total_net')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider_type')->options(array_combine(ProviderType::values(), ProviderType::values())),
                Tables\Filters\SelectFilter::make('status')->options(array_combine(SettlementStatus::values(), SettlementStatus::values())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark paid')
                    ->requiresConfirmation()
                    ->visible(fn (Settlement $record): bool => $record->status !== SettlementStatus::Paid)
                    ->action(fn (Settlement $record) => app(SettlementService::class)->markPaid(auth()->user(), $record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettlements::route('/'),
        ];
    }
}
