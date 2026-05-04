<?php

namespace App\Modules\MedicalFiles\Infrastructure\Models;

use App\Models\User;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadedFile extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'file_category',
        'visibility',
        'checksum',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'file_category' => FileCategory::class,
            'visibility' => FileVisibility::class,
            'metadata' => 'array',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
