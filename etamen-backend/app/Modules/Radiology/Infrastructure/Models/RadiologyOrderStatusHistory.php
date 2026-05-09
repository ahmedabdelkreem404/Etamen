<?php

namespace App\Modules\Radiology\Infrastructure\Models;

use App\Models\User;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyOrderStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'radiology_order_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'to_status' => RadiologyOrderStatus::class,
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RadiologyOrder::class, 'radiology_order_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
