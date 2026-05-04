<?php

namespace App\Modules\Providers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Specialty extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Specialty $specialty): void {
            if (! $specialty->slug) {
                $specialty->slug = Str::slug($specialty->name_en);
            }
        });
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(DoctorProfile::class, 'doctor_specialties')->withTimestamps();
    }
}
