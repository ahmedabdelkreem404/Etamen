<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Models\User;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDocument extends Model
{
    protected $fillable = [
        'provider_id',
        'file_id',
        'uploaded_by',
        'document_type',
        'status',
        'notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProviderDocumentStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'file_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
