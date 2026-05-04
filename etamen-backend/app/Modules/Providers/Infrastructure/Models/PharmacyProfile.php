<?php

namespace App\Modules\Providers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyProfile extends Model
{
    protected $fillable = [
        'provider_id',
        'license_number',
        'delivery_available',
    ];

    protected function casts(): array
    {
        return [
            'delivery_available' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
