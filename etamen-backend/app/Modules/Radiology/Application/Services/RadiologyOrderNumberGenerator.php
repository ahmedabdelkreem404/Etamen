<?php

namespace App\Modules\Radiology\Application\Services;

use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Illuminate\Support\Str;

class RadiologyOrderNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'RAD-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (RadiologyOrder::query()->where('order_number', $number)->exists());

        return $number;
    }
}
