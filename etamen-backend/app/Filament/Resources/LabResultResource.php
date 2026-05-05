<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabResultResource\Pages;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LabResultResource extends Resource
{
    protected static ?string $model = LabResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'Labs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_id')->disabled(),
            Forms\Components\TextInput::make('uploaded_by')->disabled(),
            Forms\Components\TextInput::make('file_id')->disabled(),
            Forms\Components\TextInput::make('title_en')->maxLength(255),
            Forms\Components\TextInput::make('title_ar')->maxLength(255),
            Forms\Components\Select::make('status')->options(array_combine(LabResultStatus::values(), LabResultStatus::values()))->required(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('order.order_number')->searchable(),
                Tables\Columns\TextColumn::make('order.lab.name_en')->label('Lab')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('file.original_name')->label('File'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(LabResultStatus::values(), LabResultStatus::values())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLabResults::route('/'),
        ];
    }
}
