<?php

namespace App\Modules\Labs\Infrastructure\Models;

use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_type',
        'test_id',
        'package_id',
        'item_name',
        'unit_price',
        'quantity',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'item_type' => LabOrderItemType::class,
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'order_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(LabPackage::class);
    }
}
