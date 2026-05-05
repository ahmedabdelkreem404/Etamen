<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PharmacyOrderResource\Pages;
use App\Modules\Pharmacies\Application\Services\PharmacyOrderService;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PharmacyOrderResource extends Resource
{
    protected static ?string $model = PharmacyOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Pharmacy';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_number')->disabled(),
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('pharmacy_provider_id')->disabled(),
            Forms\Components\TextInput::make('subtotal')->disabled(),
            Forms\Components\TextInput::make('commission_amount')->disabled(),
            Forms\Components\TextInput::make('provider_net_amount')->disabled(),
            Forms\Components\TextInput::make('grand_total')->disabled(),
            Forms\Components\TextInput::make('payment_status')->disabled(),
            Forms\Components\TextInput::make('order_status')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable(),
                Tables\Columns\TextColumn::make('pharmacy.name_en')->label('Pharmacy')->searchable(),
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('grand_total')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('provider_net_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->badge(),
                Tables\Columns\TextColumn::make('order_status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_status')->options(array_combine(PharmacyOrderStatus::values(), PharmacyOrderStatus::values())),
                Tables\Filters\SelectFilter::make('payment_status')->options(array_combine(PharmacyOrderPaymentStatus::values(), PharmacyOrderPaymentStatus::values())),
                Tables\Filters\SelectFilter::make('pharmacy_provider_id')->relationship('pharmacy', 'name_en')->label('Pharmacy'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('update_status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(array_combine(PharmacyOrderStatus::values(), PharmacyOrderStatus::values()))
                            ->required(),
                        Forms\Components\Textarea::make('reason')->maxLength(1000),
                    ])
                    ->action(fn (PharmacyOrder $record, array $data) => app(PharmacyOrderService::class)->adminUpdateStatus(
                        auth()->user(),
                        $record,
                        PharmacyOrderStatus::from($data['status']),
                        $data['reason'] ?? null,
                    )),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePharmacyOrders::route('/'),
        ];
    }
}
