<?php

namespace App\Modules\Health\Infrastructure\Models;

use App\Models\User;
use App\Modules\Health\Domain\Enums\AllergySeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAllergy extends Model
{
    protected $fillable = [
        'patient_user_id',
        'allergen',
        'reaction',
        'severity',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'severity' => AllergySeverity::class,
            'is_active' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
