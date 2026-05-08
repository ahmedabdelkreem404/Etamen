<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Models\User;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentType;
use App\Modules\Providers\Domain\Enums\ProviderDocumentVisibility;
use Illuminate\Database\Eloquent\Builder;
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
        'visibility',
        'notes',
        'reviewed_by',
        'reviewed_at',
        'approved_public_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProviderDocumentStatus::class,
            'visibility' => ProviderDocumentVisibility::class,
            'reviewed_at' => 'datetime',
            'approved_public_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProviderDocument $document): void {
            if (in_array($document->document_type, ProviderDocumentType::forcedAdminOnlyValues(), true)) {
                $document->visibility = ProviderDocumentVisibility::AdminOnly;
                $document->approved_public_at = null;
            }

            if (
                $document->visibility === ProviderDocumentVisibility::PublicCertificate
                && $document->status === ProviderDocumentStatus::Approved
                && ! $document->approved_public_at
            ) {
                $document->approved_public_at = now();
            }

            if ($document->status !== ProviderDocumentStatus::Approved || $document->visibility !== ProviderDocumentVisibility::PublicCertificate) {
                $document->approved_public_at = null;
            }
        });
    }

    public function scopePublicCertificates(Builder $query): Builder
    {
        return $query
            ->where('visibility', ProviderDocumentVisibility::PublicCertificate)
            ->where('status', ProviderDocumentStatus::Approved)
            ->whereNotNull('approved_public_at');
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
