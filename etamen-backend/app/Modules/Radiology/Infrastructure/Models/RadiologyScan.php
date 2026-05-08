<?php

namespace App\Modules\Radiology\Infrastructure\Models;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class RadiologyScan extends Model
{
    protected $fillable = [
        'provider_id',
        'branch_id',
        'radiology_scan_category_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'preparation_ar',
        'preparation_en',
        'duration_minutes',
        'base_price',
        'requires_preparation',
        'requires_fasting',
        'contrast_required',
        'home_available',
        'branch_available',
        'report_delivery_enabled',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'base_price' => 'decimal:2',
            'requires_preparation' => 'boolean',
            'requires_fasting' => 'boolean',
            'contrast_required' => 'boolean',
            'home_available' => 'boolean',
            'branch_available' => 'boolean',
            'report_delivery_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RadiologyScan $scan): void {
            $provider = Provider::query()->find($scan->provider_id);

            if (! $provider || $provider->type !== ProviderType::Radiology) {
                throw ValidationException::withMessages([
                    'provider_id' => ['The selected provider must be a radiology provider.'],
                ]);
            }

            if ($provider->status === ProviderStatus::Suspended) {
                throw ValidationException::withMessages([
                    'provider_id' => ['Suspended radiology providers cannot manage scans.'],
                ]);
            }

            if ($scan->branch_id) {
                $branchProviderId = ProviderBranch::query()->whereKey($scan->branch_id)->value('provider_id');

                if ((int) $branchProviderId !== (int) $scan->provider_id) {
                    throw ValidationException::withMessages([
                        'branch_id' => ['The selected branch does not belong to this radiology provider.'],
                    ]);
                }
            }
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('radiology_scans.is_active', true)
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true))
            ->whereHas('provider', fn (Builder $providerQuery) => $providerQuery
                ->publiclyVisible()
                ->where('type', ProviderType::Radiology));
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
        return $this->belongsTo(RadiologyScanCategory::class, 'radiology_scan_category_id');
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(RadiologyPreparationInstruction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
