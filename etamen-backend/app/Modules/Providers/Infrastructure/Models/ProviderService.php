<?php

namespace App\Modules\Providers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class ProviderService extends Model
{
    protected $fillable = [
        'provider_id',
        'branch_id',
        'service_category_id',
        'service_type',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'duration_minutes',
        'base_price',
        'online_available',
        'home_available',
        'branch_available',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'base_price' => 'decimal:2',
            'online_available' => 'boolean',
            'home_available' => 'boolean',
            'branch_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProviderService $service): void {
            if ($service->branch_id) {
                $branchProviderId = ProviderBranch::query()->whereKey($service->branch_id)->value('provider_id');

                if ((int) $branchProviderId !== (int) $service->provider_id) {
                    throw ValidationException::withMessages([
                        'branch_id' => ['The selected branch does not belong to this provider.'],
                    ]);
                }
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
