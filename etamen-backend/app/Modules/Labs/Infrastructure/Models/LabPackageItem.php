<?php

namespace App\Modules\Labs\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabPackageItem extends Model
{
    protected $fillable = [
        'package_id',
        'test_id',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(LabPackage::class, 'package_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'test_id');
    }
}
