<?php

namespace App\Modules\Health\Infrastructure\Models;

use App\Models\User;
use App\Modules\Health\Domain\Enums\VitalFlag;
use App\Modules\Health\Domain\Enums\VitalSource;
use App\Modules\Health\Domain\Enums\VitalType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalRecord extends Model
{
    protected $fillable = [
        'patient_user_id',
        'vital_type',
        'measured_at',
        'value_decimal',
        'value_secondary_decimal',
        'unit',
        'source',
        'flag',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'vital_type' => VitalType::class,
            'measured_at' => 'datetime',
            'value_decimal' => 'decimal:2',
            'value_secondary_decimal' => 'decimal:2',
            'source' => VitalSource::class,
            'flag' => VitalFlag::class,
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
