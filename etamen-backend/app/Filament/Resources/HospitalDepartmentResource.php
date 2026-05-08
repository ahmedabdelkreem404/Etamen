<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HospitalDepartmentResource\Pages;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HospitalDepartmentResource extends Resource
{
    protected static ?string $model = HospitalDepartment::class;

    protected static ?string $navigationGroup = 'Provider Foundation';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('hospital_provider_id')->relationship('hospital', 'name_en')->required(),
            Forms\Components\Select::make('specialty_id')->relationship('specialty', 'name_en'),
            Forms\Components\TextInput::make('name_ar')->required(),
            Forms\Components\TextInput::make('name_en'),
            Forms\Components\Textarea::make('description_ar')->columnSpanFull(),
            Forms\Components\Textarea::make('description_en')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('hospital.name_en')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('name_ar')->searchable(),
            Tables\Columns\TextColumn::make('specialty.name_en')->searchable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHospitalDepartments::route('/'),
        ];
    }
}
