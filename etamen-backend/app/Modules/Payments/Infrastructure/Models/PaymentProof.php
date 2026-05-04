<?php

namespace App\Modules\Payments\Infrastructure\Models;

use App\Models\User;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use App\Modules\Payments\Domain\Enums\PaymentProofStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'payment_id',
        'uploaded_by',
        'file_id',
        'reference_number',
        'sender_phone',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentProofStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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
