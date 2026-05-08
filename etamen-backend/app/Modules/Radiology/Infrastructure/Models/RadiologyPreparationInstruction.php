<?php

namespace App\Modules\Radiology\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class RadiologyPreparationInstruction extends Model
{
    public const DISCLAIMER_AR = 'هذه تعليمات عامة ولا تغني عن تعليمات المركز أو الطبيب.';

    public const DISCLAIMER_EN = 'These are general instructions and do not replace the center or doctor instructions.';

    protected $fillable = [
        'radiology_scan_category_id',
        'radiology_scan_id',
        'title_ar',
        'title_en',
        'body_ar',
        'body_en',
        'warning_ar',
        'warning_en',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RadiologyPreparationInstruction $instruction): void {
            if (! $instruction->radiology_scan_category_id && ! $instruction->radiology_scan_id) {
                throw ValidationException::withMessages([
                    'radiology_scan_category_id' => ['Either category or scan is required.'],
                ]);
            }

            $instruction->warning_ar = static::appendDisclaimer($instruction->warning_ar, self::DISCLAIMER_AR);
            $instruction->warning_en = static::appendDisclaimer($instruction->warning_en, self::DISCLAIMER_EN);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RadiologyScanCategory::class, 'radiology_scan_category_id');
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(RadiologyScan::class, 'radiology_scan_id');
    }

    private static function appendDisclaimer(?string $value, string $disclaimer): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $disclaimer;
        }

        if (str_contains($value, $disclaimer)) {
            return $value;
        }

        return $value."\n\n".$disclaimer;
    }
}
