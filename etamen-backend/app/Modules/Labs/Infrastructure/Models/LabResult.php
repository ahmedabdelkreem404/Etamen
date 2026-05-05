<?php

namespace App\Modules\Labs\Infrastructure\Models;

use App\Models\User;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    protected $fillable = [
        'order_id',
        'uploaded_by',
        'file_id',
        'title_ar',
        'title_en',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => LabResultStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'order_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'file_id');
    }
}
