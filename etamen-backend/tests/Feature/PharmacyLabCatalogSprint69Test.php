<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PharmacyLabCatalogSprint69Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_patient_pharmacy_catalog_search_filter_sort_and_hidden_inactive_products(): void
    {
        [, $pharmacy] = $this->providerWithOwner(ProviderType::Pharmacy);
        $this->product($pharmacy, 'Vitamin C Demo', 90, false, 20, true, 'vitamins');
        $rx = $this->product($pharmacy, 'Antibiotic RX Demo', 160, true, 8, true, 'prescription');
        $this->product($pharmacy, 'Inactive Private Demo', 35, false, 10, false, 'private');

        $this->getJson('/api/v1/pharmacies/'.$pharmacy->id.'/products?search=Vitamin')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name_en', 'Vitamin C Demo')
            ->assertJsonMissing(['name_en' => 'Inactive Private Demo']);

        $this->getJson('/api/v1/pharmacies/'.$pharmacy->id.'/products?requires_prescription=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $rx->id)
            ->assertJsonPath('data.0.requires_prescription', true);

        $prices = collect($this->getJson('/api/v1/pharmacies/'.$pharmacy->id.'/products?min_price=80&max_price=200&sort=price_asc')
            ->assertOk()
            ->json('data'))
            ->pluck('price')
            ->map(fn ($price) => (float) $price)
            ->values()
            ->all();

        $this->assertSame($prices, collect($prices)->sort()->values()->all());
        $this->getJson('/api/v1/pharmacies/'.$pharmacy->id.'/products?sort=bad')->assertUnprocessable();
    }

    public function test_pharmacy_catalog_pagination_cap_and_provider_workspace_filters(): void
    {
        [$owner, $pharmacy] = $this->providerWithOwner(ProviderType::Pharmacy);
        [, $otherPharmacy] = $this->providerWithOwner(ProviderType::Pharmacy);

        foreach (range(1, 105) as $index) {
            $this->product($pharmacy, 'Bulk Product '.$index, 10 + $index, false, 5, true, 'bulk', 'BULK-'.$index);
        }
        $inactive = $this->product($pharmacy, 'Inactive Provider Item', 55, false, 12, false, 'private', 'INACTIVE-PROVIDER');

        $this->getJson('/api/v1/pharmacies/'.$pharmacy->id.'/products?per_page=500')
            ->assertOk()
            ->assertJsonCount(100, 'data')
            ->assertJsonMissing(['id' => $inactive->id]);

        Sanctum::actingAs($owner);
        $this->getJson('/api/v1/provider/workspace/'.$pharmacy->id.'/pharmacy/products?active=0&per_page=200')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $inactive->id)
            ->assertJsonPath('data.items.0.is_active', false);

        $this->getJson('/api/v1/provider/workspace/'.$pharmacy->id.'/pharmacy/products?requires_prescription=maybe')->assertUnprocessable();

        [$wrongOwner] = $this->providerWithOwner(ProviderType::Lab);
        Sanctum::actingAs($wrongOwner);
        $this->getJson('/api/v1/provider/workspace/'.$pharmacy->id.'/pharmacy/products')->assertForbidden();
        $this->getJson('/api/v1/provider/workspace/'.$otherPharmacy->id.'/pharmacy/products')->assertForbidden();
    }

    public function test_patient_lab_catalog_search_filters_sort_and_hidden_inactive_items(): void
    {
        [, $lab] = $this->providerWithOwner(ProviderType::Lab);
        $fast = $this->labTest($lab, 'Fast Glucose Demo', 80, 'blood', 6, true, 'FAST-GLUCOSE');
        $slow = $this->labTest($lab, 'Vitamin D Demo', 420, 'blood', 72, true, 'VIT-D');
        $inactive = $this->labTest($lab, 'Inactive Lab Test', 50, 'blood', 24, false, 'INACTIVE-LAB');
        $package = $this->labPackage($lab, 'Basic Checkup Demo', 300, true, [$fast, $slow]);
        $inactivePackage = $this->labPackage($lab, 'Inactive Package Demo', 90, false, [$inactive]);

        $this->getJson('/api/v1/labs/'.$lab->id.'/tests?search=Glucose')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $fast->id)
            ->assertJsonMissing(['id' => $inactive->id]);

        $this->getJson('/api/v1/labs/'.$lab->id.'/tests?sample_type=blood&result_time_max_hours=12')
            ->assertOk()
            ->assertJsonFragment(['id' => $fast->id])
            ->assertJsonMissing(['id' => $slow->id]);

        $packageResponse = $this->getJson('/api/v1/labs/'.$lab->id.'/packages?search=Checkup&sort=result_time')
            ->assertOk()
            ->assertJsonFragment(['id' => $package->id])
            ->assertJsonMissing(['name_en' => $inactivePackage->name_en]);
        $this->assertSafeCatalogResponse($packageResponse->content());

        $prices = collect($this->getJson('/api/v1/labs/'.$lab->id.'/tests?min_price=60&max_price=500&sort=price_desc')
            ->assertOk()
            ->json('data'))
            ->pluck('price')
            ->map(fn ($price) => (float) $price)
            ->values()
            ->all();

        $this->assertSame($prices, collect($prices)->sortDesc()->values()->all());
        $this->getJson('/api/v1/labs/'.$lab->id.'/tests?result_time_max_hours=bad')->assertUnprocessable();
    }

    public function test_provider_lab_catalog_filters_wrong_provider_and_safe_metadata(): void
    {
        [$owner, $lab] = $this->providerWithOwner(ProviderType::Lab);
        $fast = $this->labTest($lab, 'Fast CBC Demo', 120, 'blood', 8, true, 'FAST-CBC');
        $inactive = $this->labTest($lab, 'Inactive Hidden Provider Test', 70, 'urine', 24, false, 'INACTIVE-PROVIDER-LAB');
        $this->labPackage($lab, 'Fast Package Demo', 210, true, [$fast]);

        Sanctum::actingAs($owner);
        $response = $this->getJson('/api/v1/provider/workspace/'.$lab->id.'/lab/catalog?type=test&active=0&per_page=200')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $inactive->id)
            ->assertJsonPath('data.items.0.catalog_type', 'test');
        $this->assertSafeCatalogResponse($response->content());

        $this->getJson('/api/v1/provider/workspace/'.$lab->id.'/lab/catalog?sort=bad')->assertUnprocessable();

        [$wrongOwner] = $this->providerWithOwner(ProviderType::Pharmacy);
        Sanctum::actingAs($wrongOwner);
        $this->getJson('/api/v1/provider/workspace/'.$lab->id.'/lab/catalog')->assertForbidden();
    }

    private function providerWithOwner(ProviderType $type): array
    {
        $owner = User::factory()->create();
        $owner->assignRole(UserRole::ProviderAdmin->value);

        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $owner->id,
            'name_ar' => 'Sprint 69 '.$type->value,
            'name_en' => 'Sprint 69 '.$type->value,
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
        ]);

        ProviderStaff::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $owner->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
            'permissions' => [
                ProviderPermission::ManagePharmacyProducts->value,
                ProviderPermission::ManageLabCatalog->value,
            ],
        ]);

        return [$owner, $provider];
    }

    private function product(Provider $provider, string $name, int $price, bool $rx, int $stock, bool $active, string $category, ?string $sku = null): PharmacyProduct
    {
        return PharmacyProduct::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => $name,
            'name_en' => $name,
            'description_ar' => 'Sprint 69 demo pharmacy item.',
            'description_en' => 'Sprint 69 demo pharmacy item.',
            'sku' => $sku ?? 'S69-'.$provider->id.'-'.str_replace(' ', '-', strtoupper($name)),
            'price' => $price,
            'requires_prescription' => $rx,
            'stock_quantity' => $stock,
            'is_active' => $active,
            'metadata' => ['category' => $category],
        ]);
    }

    private function labTest(Provider $provider, string $name, int $price, string $sampleType, int $hours, bool $active, string $code): LabTest
    {
        return LabTest::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => $name,
            'name_en' => $name,
            'description_ar' => 'Sprint 69 demo lab catalog item. Metadata only.',
            'description_en' => 'Sprint 69 demo lab catalog item. Metadata only.',
            'code' => $code,
            'price' => $price,
            'sample_type' => $sampleType,
            'preparation_instructions_ar' => 'Demo preparation only.',
            'preparation_instructions_en' => 'Demo preparation only.',
            'result_time_hours' => $hours,
            'is_active' => $active,
        ]);
    }

    private function labPackage(Provider $provider, string $name, int $price, bool $active, array $tests): LabPackage
    {
        $package = LabPackage::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => $name,
            'name_en' => $name,
            'description_ar' => 'Sprint 69 demo package. Metadata only.',
            'description_en' => 'Sprint 69 demo package. Metadata only.',
            'price' => $price,
            'is_active' => $active,
        ]);

        $package->tests()->sync(collect($tests)->pluck('id')->all());

        return $package;
    }

    private function assertSafeCatalogResponse(string $content): void
    {
        $this->assertStringNotContainsString('storage/private', $content);
        $this->assertStringNotContainsString('proof_path', $content);
        $this->assertStringNotContainsString('result_path', $content);
        $this->assertStringNotContainsString('prescription_path', $content);
        $this->assertStringNotContainsString('payment_config', $content);
        $this->assertStringNotContainsString('APP_KEY', $content);
        $this->assertStringNotContainsString('DB_PASSWORD', $content);
        $this->assertStringNotContainsString('interpretation', $content);
        $this->assertStringNotContainsString('diagnosis', $content);
    }
}
