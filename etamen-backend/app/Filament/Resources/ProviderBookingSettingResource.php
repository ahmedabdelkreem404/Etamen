<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderBookingSettingResource\Pages;
use App\Modules\Providers\Infrastructure\Models\ProviderBookingSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProviderBookingSettingResource extends Resource
{
    protected static ?string $model = ProviderBookingSetting::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name_en')->required(),
            Forms\Components\Toggle::make('clinic_visit_enabled'),
            Forms\Components\Toggle::make('online_video_enabled'),
            Forms\Components\Toggle::make('home_visit_enabled'),
            Forms\Components\Toggle::make('branch_visit_enabled'),
            Forms\Components\Toggle::make('booking_requires_payment'),
            Forms\Components\Toggle::make('pay_at_branch_enabled'),
            Forms\Components\TextInput::make('default_slot_duration_minutes')->numeric(),
            Forms\Components\Textarea::make('cancellation_policy_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('cancellation_policy_en')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('provider.name_en')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('clinic_visit_enabled')->boolean(),
            Tables\Columns\IconColumn::make('online_video_enabled')->boolean(),
            Tables\Columns\IconColumn::make('home_visit_enabled')->boolean(),
            Tables\Columns\IconColumn::make('pay_at_branch_enabled')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProviderBookingSettings::route('/'),
        ];
    }
}
