<?php

namespace App\Modules\Health\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientChronicDisease extends Model
{
    protected $fillable = [
        'patient_user_id',
        'name',
        'diagnosed_at',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'diagnosed_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
