<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabOrderResource\Pages;
use App\Modules\Labs\Application\Services\LabOrderService;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LabOrderResource extends Resource
{
    protected static ?string $model = LabOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Labs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_number')->disabled(),
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('lab_provider_id')->disabled(),
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
                Tables\Columns\TextColumn::make('lab.name_en')->label('Lab')->searchable(),
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('grand_total')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('provider_net_amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->badge(),
                Tables\Columns\TextColumn::make('order_status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_status')->options(array_combine(LabOrderStatus::values(), LabOrderStatus::values())),
                Tables\Filters\SelectFilter::make('payment_status')->options(array_combine(LabOrderPaymentStatus::values(), LabOrderPaymentStatus::values())),
                Tables\Filters\SelectFilter::make('lab_provider_id')->relationship('lab', 'name_en')->label('Lab'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('update_status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(array_combine(LabOrderStatus::values(), LabOrderStatus::values()))
                            ->required(),
                        Forms\Components\Textarea::make('reason')->maxLength(1000),
                    ])
                    ->action(fn (LabOrder $record, array $data) => app(LabOrderService::class)->adminUpdateStatus(
                        auth()->user(),
                        $record,
                        LabOrderStatus::from($data['status']),
                        $data['reason'] ?? null,
                    )),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLabOrders::route('/'),
        ];
    }
}
