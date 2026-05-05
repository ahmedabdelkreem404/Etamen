<?php

namespace App\Modules\Payments\Application\Services;

use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Support\Str;

class InvoiceService
{
    public function createForPayment(Payment $payment): Invoice
    {
        return Invoice::query()->firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'invoice_number' => $this->generateNumber(),
                'gross_amount' => $payment->amount,
                'commission_amount' => 0,
                'net_amount' => $payment->amount,
                'currency' => $payment->currency,
                'issued_at' => now(),
            ],
        );
    }

    private function generateNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Invoice::query()->where('invoice_number', $number)->exists());

        return $number;
    }
}
