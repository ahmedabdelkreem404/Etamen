<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchedulerRunResource\Pages;
use App\Modules\Notifications\Domain\Enums\SchedulerRunStatus;
use App\Modules\Notifications\Infrastructure\Models\SchedulerRun;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchedulerRunResource extends Resource
{
    protected static ?string $model = SchedulerRun::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Notifications';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('job_name')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('processed_count')->disabled(),
            Forms\Components\TextInput::make('failed_count')->disabled(),
            Forms\Components\Textarea::make('error_message')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('job_name')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('processed_count')->sortable(),
            Tables\Columns\TextColumn::make('failed_count')->sortable(),
            Tables\Columns\TextColumn::make('started_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('finished_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(array_combine(SchedulerRunStatus::values(), SchedulerRunStatus::values())),
        ])->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageSchedulerRuns::route('/')];
    }
}
