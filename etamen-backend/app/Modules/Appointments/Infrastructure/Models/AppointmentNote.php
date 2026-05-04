<?php

namespace App\Modules\Appointments\Infrastructure\Models;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentNoteVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentNote extends Model
{
    protected $fillable = [
        'appointment_id',
        'author_id',
        'note',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => AppointmentNoteVisibility::class,
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
