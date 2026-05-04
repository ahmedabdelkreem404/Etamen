<?php

namespace App\Modules\Providers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabProfile extends Model
{
    protected $fillable = [
        'provider_id',
        'license_number',
        'home_collection_available',
    ];

    protected function casts(): array
    {
        return [
            'home_collection_available' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
