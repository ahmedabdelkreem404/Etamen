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
                PaymentMethodType::ManualVodafoneCash->value,
                PaymentMethodType::ManualInstapay->value,
                PaymentMethodType::Paymob->value,
            ],
            PaymentMethod::query()->orderBy('sort_order')->pluck('type')->map(fn ($type) => $type->value)->all(),
        );
    }

    public function test_payment_methods_endpoint_returns_active_manual_methods_only_and_hides_config(): void
    {
        $this->seed(PaymentMethodSeeder::class);

        PaymentMethod::query()
            ->where('type', PaymentMethodType::ManualVodafoneCash)
            ->update(['config' => ['internal_note' => 'not-public']]);

        $response = $this->getJson('/api/v1/payment-methods')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', PaymentMethodType::ManualVodafoneCash->value)
            ->assertJsonPath('data.1.type', PaymentMethodType::ManualInstapay->value)
            ->assertJsonMissing(['type' => PaymentMethodType::Paymob->value]);

        foreach ($response->json('data') as $method) {
            $this->assertArrayNotHasKey('config', $method);
        }
    }

    public function test_inactive_methods_are_hidden_from_public_endpoint(): void
    {
        $this->seed(PaymentMethodSeeder::class);

        PaymentMethod::query()
            ->where('type', PaymentMethodType::ManualInstapay)
            ->update(['is_active' => false]);

        $this->getJson('/api/v1/payment-methods')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', PaymentMethodType::ManualVodafoneCash->value)
            ->assertJsonMissing(['type' => PaymentMethodType::ManualInstapay->value]);
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

    public function test_ensure_payment_methods_command_restores_active_manual_methods(): void
    {
        PaymentMethod::query()->delete();

        $this->artisan('etamen:ensure-payment-methods --staging')
            ->assertSuccessful();

        $this->assertDatabaseHas('payment_methods', [
            'type' => PaymentMethodType::ManualVodafoneCash->value,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('payment_methods', [
            'type' => PaymentMethodType::ManualInstapay->value,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('payment_methods', [
            'type' => PaymentMethodType::Paymob->value,
            'is_active' => false,
        ]);
    }
}
