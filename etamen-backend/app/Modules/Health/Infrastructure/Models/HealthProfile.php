<?php

namespace App\Modules\Health\Infrastructure\Models;

use App\Models\User;
use App\Modules\Health\Domain\Enums\BloodType;
use App\Modules\Health\Domain\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthProfile extends Model
{
    protected $fillable = [
        'patient_user_id',
        'date_of_birth',
        'gender',
        'height_cm',
        'weight_kg',
        'blood_type',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => Gender::class,
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'blood_type' => BloodType::class,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
