<?php

use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('etamen:ensure-payment-methods {--staging : Ensure staging-safe manual methods are active}', function (): int {
    $this->call('db:seed', [
        '--class' => PaymentMethodSeeder::class,
        '--force' => true,
    ]);

    $manualMethods = PaymentMethod::query()
        ->whereIn('type', [
            PaymentMethodType::ManualVodafoneCash->value,
            PaymentMethodType::ManualInstapay->value,
        ])
        ->orderBy('sort_order')
        ->get(['type', 'name_en', 'is_active']);

    foreach ($manualMethods as $method) {
        $this->line(sprintf('%s: %s', $method->type->value, $method->is_active ? 'active' : 'inactive'));
    }

    $paymob = PaymentMethod::query()->where('type', PaymentMethodType::Paymob->value)->first();
    $this->line('paymob: '.($paymob?->is_active ? 'active' : 'inactive'));

    return 0;
})->purpose('Ensure the known Etamen payment methods exist with staging-safe manual methods active.');
