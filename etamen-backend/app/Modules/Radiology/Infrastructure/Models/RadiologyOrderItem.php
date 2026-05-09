<?php

namespace App\Modules\Radiology\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyOrderItem extends Model
{
    protected $fillable = [
        'radiology_order_id',
        'radiology_scan_id',
        'scan_name_ar',
        'scan_name_en',
        'category_name_ar',
        'category_name_en',
        'unit_price',
        'quantity',
        'total_price',
        'preparation_snapshot_ar',
        'preparation_snapshot_en',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RadiologyOrder::class, 'radiology_order_id');
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(RadiologyScan::class, 'radiology_scan_id');
    }
}
