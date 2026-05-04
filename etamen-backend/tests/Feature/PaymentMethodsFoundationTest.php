<?php

namespace Tests\Feature;

use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodsFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_three_allowed_payment_method_types_exist(): void
    {
        $this->seed(PaymentMethodSeeder::class);

        $this->assertSame(
            [
                PaymentMethodType::Paymob->value,
                PaymentMethodType::ManualVodafoneCash->value,
                PaymentMethodType::ManualInstapay->value,
            ],
            PaymentMethod::query()->orderBy('sort_order')->pluck('type')->map(fn ($type) => $type->value)->all(),
        );
    }

    public function test_payment_methods_endpoint_returns_active_methods_only(): void
    {
        $this->seed(PaymentMethodSeeder::class);

        PaymentMethod::query()
            ->where('type', PaymentMethodType::Paymob)
            ->update(['is_active' => true]);

        $this->getJson('/api/v1/payment-methods')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', PaymentMethodType::Paymob->value);
    }

    public function test_seeders_do_not_create_any_non_allowed_payment_method_types(): void
    {
        $this->seed(PaymentMethodSeeder::class);

        $allowed = collect(PaymentMethodType::cases())
            ->map(fn (PaymentMethodType $type): string => $type->value)
            ->sort()
            ->values();

        $seeded = PaymentMethod::query()
            ->pluck('type')
            ->map(fn (PaymentMethodType $type): string => $type->value)
            ->sort()
            ->values();

        $this->assertEquals($allowed, $seeded);
    }
}
