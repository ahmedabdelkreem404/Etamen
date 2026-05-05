<?php

namespace App\Modules\Pharmacies\Infrastructure\Models;

use App\Models\User;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyPrescription extends Model
{
    protected $fillable = [
        'patient_user_id',
        'pharmacy_provider_id',
        'uploaded_file_id',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'pharmacy_provider_id');
    }

    public function uploadedFile(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'uploaded_file_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PharmacyOrder::class, 'prescription_id');
    }
}
