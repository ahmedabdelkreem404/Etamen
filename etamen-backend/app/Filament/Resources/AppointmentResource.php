<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Modules\Appointments\Application\Services\AdminAppointmentService;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Appointments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('appointment_number')->disabled(),
            Forms\Components\TextInput::make('patient_user_id')->disabled(),
            Forms\Components\TextInput::make('doctor_profile_id')->disabled(),
            Forms\Components\TextInput::make('provider_id')->disabled(),
            Forms\Components\TextInput::make('appointment_slot_id')->disabled(),
            Forms\Components\Select::make('consultation_type')->options(array_combine(ConsultationType::values(), ConsultationType::values()))->disabled(),
            Forms\Components\Textarea::make('problem_description')->disabled()->columnSpanFull(),
            Forms\Components\TextInput::make('price')->disabled(),
            Forms\Components\TextInput::make('currency')->disabled(),
            Forms\Components\Select::make('status')->options(array_combine(AppointmentStatus::values(), AppointmentStatus::values()))->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment_number')->searchable(),
                Tables\Columns\TextColumn::make('patient.name')->label('Patient')->searchable(),
                Tables\Columns\TextColumn::make('provider.name_en')->label('Provider')->searchable(),
                Tables\Columns\TextColumn::make('doctor_profile_id')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->searchable(),
                Tables\Columns\TextColumn::make('price')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(array_combine(AppointmentStatus::values(), AppointmentStatus::values())),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('force_cancel')
                    ->label('Force cancel')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')->required()->maxLength(1000),
                    ])
                    ->action(fn (Appointment $record, array $data) => app(AdminAppointmentService::class)->forceCancel(auth()->user(), $record, $data['reason'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAppointments::route('/'),
        ];
    }
}
