<?php

namespace App\Modules\Health\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientSurgery extends Model
{
    protected $fillable = [
        'patient_user_id',
        'surgery_name',
        'surgery_date',
        'hospital_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'surgery_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
