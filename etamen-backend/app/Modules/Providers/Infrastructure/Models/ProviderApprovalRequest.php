<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderApprovalRequest extends Model
{
    protected $fillable = [
        'provider_id',
        'requested_by',
        'reviewed_by',
        'status',
        'notes',
        'review_notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
