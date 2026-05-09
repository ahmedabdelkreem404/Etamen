<?php

namespace App\Modules\Radiology\Infrastructure\Models;

use App\Models\User;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use App\Modules\Radiology\Domain\Enums\RadiologyResultType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyResult extends Model
{
    protected $fillable = [
        'radiology_order_id',
        'uploaded_file_id',
        'uploaded_by',
        'result_type',
        'title_ar',
        'title_en',
        'notes_ar',
        'notes_en',
        'is_visible_to_patient',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'result_type' => RadiologyResultType::class,
            'is_visible_to_patient' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RadiologyOrder::class, 'radiology_order_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'uploaded_file_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
