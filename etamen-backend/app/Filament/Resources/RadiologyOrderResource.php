<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiologyOrderResource\Pages;
use App\Modules\Radiology\Application\Services\RadiologyOrderService;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RadiologyOrderResource extends Resource
{
    protected static ?string $model = RadiologyOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Radiology';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_number')->disabled(),
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('provider_id')->disabled(),
            Forms\Components\TextInput::make('branch_id')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('subtotal')->disabled(),
            Forms\Components\TextInput::make('discount_amount')->disabled(),
            Forms\Components\TextInput::make('total_amount')->disabled(),
            Forms\Components\TextInput::make('payment_id')->disabled(),
            Forms\Components\Textarea::make('patient_notes')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('provider_notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable(),
                Tables\Columns\TextColumn::make('provider.name_en')->label('Radiology')->searchable(),
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('total_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('payment.status')->label('Payment')->badge(),
                Tables\Columns\TextColumn::make('scheduled_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(RadiologyOrderStatus::values(), RadiologyOrderStatus::values())),
                Tables\Filters\SelectFilter::make('provider_id')->relationship('provider', 'name_en')->label('Radiology Provider'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('force_cancel')
                    ->requiresConfirmation()
                    ->form([Forms\Components\Textarea::make('reason')->maxLength(1000)])
                    ->action(fn (RadiologyOrder $record, array $data) => app(RadiologyOrderService::class)->forceCancel(
                        auth()->user(),
                        $record,
                        $data['reason'] ?? null,
                    )),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRadiologyOrders::route('/'),
        ];
    }
}
