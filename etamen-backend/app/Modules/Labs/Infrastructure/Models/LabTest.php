<?php

namespace App\Modules\Labs\Infrastructure\Models;

use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LabTest extends Model
{
    protected $fillable = [
        'provider_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'code',
        'price',
        'sample_type',
        'preparation_instructions_ar',
        'preparation_instructions_en',
        'result_time_hours',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'result_time_hours' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(LabPackage::class, 'lab_package_items', 'test_id', 'package_id')
            ->withTimestamps();
    }
}
