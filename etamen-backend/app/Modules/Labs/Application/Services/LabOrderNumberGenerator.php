<?php

namespace App\Modules\Labs\Application\Services;

use App\Modules\Labs\Infrastructure\Models\LabOrder;
use Illuminate\Support\Str;

class LabOrderNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'LAB-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (LabOrder::query()->where('order_number', $number)->exists());

        return $number;
    }
}
