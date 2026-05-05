<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarePlanInstructionResource\Pages;
use App\Modules\CarePlans\Domain\Enums\CarePlanInstructionType;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanInstruction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarePlanInstructionResource extends Resource
{
    protected static ?string $model = CarePlanInstruction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Care Plans';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('plan.title')->disabled(),
            Forms\Components\TextInput::make('instruction_type')->disabled(),
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\Textarea::make('body')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.title')->searchable(),
                Tables\Columns\TextColumn::make('instruction_type')->badge(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('instruction_type')->options(array_combine(CarePlanInstructionType::values(), CarePlanInstructionType::values())),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCarePlanInstructions::route('/')];
    }
}
