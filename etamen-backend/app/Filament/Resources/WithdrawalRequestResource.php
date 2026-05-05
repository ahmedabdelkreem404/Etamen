<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawalRequestResource\Pages;
use App\Modules\Wallets\Application\Services\WithdrawalService;
use App\Modules\Wallets\Domain\Enums\WithdrawalRequestStatus;
use App\Modules\Wallets\Infrastructure\Models\WithdrawalRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WithdrawalRequestResource extends Resource
{
    protected static ?string $model = WithdrawalRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Wallets';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('wallet_id')->disabled(),
            Forms\Components\TextInput::make('amount')->disabled(),
            Forms\Components\Select::make('status')
                ->options(array_combine(WithdrawalRequestStatus::values(), WithdrawalRequestStatus::values()))
                ->disabled(),
            Forms\Components\TextInput::make('requested_by')->disabled(),
            Forms\Components\TextInput::make('reviewed_by')->disabled(),
            Forms\Components\Textarea::make('rejection_reason')->disabled()->columnSpanFull(),
            Forms\Components\DateTimePicker::make('paid_at')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('wallet_id')->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(WithdrawalRequestStatus::values(), WithdrawalRequestStatus::values())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->requiresConfirmation()
                    ->visible(fn (WithdrawalRequest $record): bool => $record->status === WithdrawalRequestStatus::Pending)
                    ->action(fn (WithdrawalRequest $record) => app(WithdrawalService::class)->approve(auth()->user(), $record)),
                Tables\Actions\Action::make('reject')
                    ->requiresConfirmation()
                    ->visible(fn (WithdrawalRequest $record): bool => in_array($record->status, [WithdrawalRequestStatus::Pending, WithdrawalRequestStatus::Approved], true))
                    ->form([
                        Forms\Components\Textarea::make('reason')->required()->maxLength(1000),
                    ])
                    ->action(fn (WithdrawalRequest $record, array $data) => app(WithdrawalService::class)->reject(auth()->user(), $record, $data['reason'])),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark paid')
                    ->requiresConfirmation()
                    ->visible(fn (WithdrawalRequest $record): bool => $record->status === WithdrawalRequestStatus::Approved)
                    ->action(fn (WithdrawalRequest $record) => app(WithdrawalService::class)->markPaid(auth()->user(), $record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWithdrawalRequests::route('/'),
        ];
    }
}
