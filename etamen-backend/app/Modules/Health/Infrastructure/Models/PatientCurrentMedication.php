<?php

namespace App\Modules\Health\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCurrentMedication extends Model
{
    protected $fillable = [
        'patient_user_id',
        'medication_name',
        'dosage',
        'frequency_text',
        'started_at',
        'ended_at',
        'prescribed_by',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
