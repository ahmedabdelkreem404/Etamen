<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PharmacyPrescriptionResource\Pages;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyPrescription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PharmacyPrescriptionResource extends Resource
{
    protected static ?string $model = PharmacyPrescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'Pharmacy';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('pharmacy_provider_id')->disabled(),
            Forms\Components\TextInput::make('uploaded_file_id')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('patient.email')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('pharmacy.name_en')->label('Pharmacy')->searchable(),
                Tables\Columns\TextColumn::make('uploadedFile.original_name')->label('File'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePharmacyPrescriptions::route('/'),
        ];
    }
}
