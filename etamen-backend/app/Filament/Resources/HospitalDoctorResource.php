<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HospitalDoctorResource\Pages;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HospitalDoctorResource extends Resource
{
    protected static ?string $model = HospitalDoctor::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('hospital_provider_id')->relationship('hospital', 'name_en')->required(),
            Forms\Components\Select::make('doctor_provider_id')->relationship('doctorProvider', 'name_en')->required(),
            Forms\Components\Select::make('hospital_department_id')->relationship('department', 'name_ar'),
            Forms\Components\TextInput::make('consultation_fee')->numeric(),
            Forms\Components\Toggle::make('online_consultation_enabled'),
            Forms\Components\Toggle::make('clinic_consultation_enabled'),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('hospital.name_en')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('doctorProvider.name_en')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('department.name_ar')->searchable(),
            Tables\Columns\TextColumn::make('consultation_fee')->money('EGP'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHospitalDoctors::route('/'),
        ];
    }
}
