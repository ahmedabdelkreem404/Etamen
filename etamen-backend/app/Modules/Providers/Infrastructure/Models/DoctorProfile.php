<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DoctorProfile extends Model
{
    protected $fillable = [
        'provider_id',
        'user_id',
        'title',
        'bio_ar',
        'bio_en',
        'consultation_fee',
        'years_of_experience',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'years_of_experience' => 'integer',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialties')->withTimestamps();
    }
}
