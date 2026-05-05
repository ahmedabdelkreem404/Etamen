<?php

namespace App\Modules\Pharmacies\Application\Services;

use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use Illuminate\Support\Str;

class PharmacyOrderNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'PHO-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (PharmacyOrder::query()->where('order_number', $number)->exists());

        return $number;
    }
}
