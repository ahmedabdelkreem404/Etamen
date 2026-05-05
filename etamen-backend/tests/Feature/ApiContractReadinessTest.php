<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiConversationStatus;
use App\Modules\AI\Domain\Enums\AiLanguage;
use App\Modules\AI\Domain\Enums\AiMessageRole;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Notifications\Application\Services\NotificationService;
use App\Modules\Notifications\Database\Seeders\NotificationTemplateSeeder;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyPrescription;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiContractReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
    }

    public function test_auth_and_standard_error_contract_shapes_are_stable(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);

        $this->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);

        $patient = $this->patientUser('contract-login@example.com');

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $patient->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'roles', 'created_at'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->withHeader('Authorization', 'Bearer '.$login->json('data.token'))
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $patient->id)
            ->assertJsonPath('data.roles.0', UserRole::Patient->value);

        Sanctum::actingAs($patient);

        $this->getJson('/api/v1/admin/providers')
            ->assertForbidden()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);

        $this->getJson('/api/v1/not-a-real-contract-route')
            ->assertNotFound()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
    }

    public function test_critical_mobile_lists_enforce_per_page_cap(): void
    {
        for ($index = 0; $index < 105; $index++) {
            $this->createProvider(ProviderType::Doctor, ['name_en' => 'Contract Doctor '.$index]);
        }

        $pharmacy = $this->createProvider(ProviderType::Pharmacy);
        for ($index = 0; $index < 105; $index++) {
            PharmacyProduct::query()->create([
                'provider_id' => $pharmacy->id,
                'name_en' => 'Contract Product '.$index,
                'price' => 20 + $index,
                'stock_quantity' => 10,
                'requires_prescription' => false,
                'is_active' => true,
            ]);
        }

        $patient = $this->patientUser('contract-notifications@example.com');
        for ($index = 0; $index < 120; $index++) {
            Notification::query()->create([
                'user_id' => $patient->id,
                'category' => NotificationCategory::System,
                'type' => 'system_notice',
                'title' => 'Notice',
                'body' => 'Body',
                'priority' => NotificationPriority::Normal,
            ]);
        }

        $this->getJson('/api/v1/doctors?per_page=250')
            ->assertOk()
            ->assertJsonCount(100, 'data');

        $this->getJson('/api/v1/pharmacies/'.$pharmacy->id.'/products?per_page=250')
            ->assertOk()
            ->assertJsonCount(100, 'data');

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/notifications?per_page=250')
            ->assertOk()
            ->assertJsonCount(100, 'data');
    }

    public function test_sensitive_resources_hide_internal_contract_fields(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser('contract-privacy@example.com');
        Sanctum::actingAs($patient);

        $payment = Payment::query()->create([
            'user_id' => $patient->id,
            'payment_method_id' => PaymentMethod::query()->where('type', PaymentMethodType::Paymob)->value('id'),
            'amount' => 100,
            'currency' => 'EGP',
            'status' => PaymentStatus::PendingGateway,
            'metadata' => [
                'gateway_api_key' => 'paymob-secret-key',
                'raw_payload' => ['card' => '4111111111111111'],
            ],
        ]);
        $payment->attempts()->create([
            'method_type' => PaymentMethodType::Paymob,
            'gateway_reference' => 'contract-gateway-ref',
            'request_payload' => ['authorization' => 'Bearer should-not-leak'],
            'response_payload' => ['raw_response' => 'provider raw payload'],
            'status' => 'created',
        ]);

        $paymentResponse = $this->getJson('/api/v1/payments/'.$payment->id.'/status')
            ->assertOk()
            ->assertJsonPath('data.id', $payment->id)
            ->assertJsonMissingPath('data.metadata')
            ->assertJsonMissingPath('data.attempts');

        $this->assertStringNotContainsString('paymob-secret-key', $paymentResponse->getContent());
        $this->assertStringNotContainsString('provider raw payload', $paymentResponse->getContent());
        $this->assertStringNotContainsString('4111111111111111', $paymentResponse->getContent());

        $pharmacy = $this->createProvider(ProviderType::Pharmacy);
        $pharmacyOrder = PharmacyOrder::query()->create([
            'order_number' => 'PH-CONTRACT-'.Str::upper(Str::random(6)),
            'patient_user_id' => $patient->id,
            'pharmacy_provider_id' => $pharmacy->id,
            'subtotal' => 100,
            'discount_total' => 0,
            'commission_amount' => 15,
            'provider_net_amount' => 85,
            'grand_total' => 100,
            'currency' => 'EGP',
            'payment_status' => PharmacyOrderPaymentStatus::Unpaid,
            'order_status' => PharmacyOrderStatus::PharmacyReview,
            'delivery_method' => PharmacyDeliveryMethod::Pickup,
        ]);

        $this->getJson('/api/v1/pharmacy/orders/'.$pharmacyOrder->id)
            ->assertOk()
            ->assertJsonPath('data.grand_total', '100.00')
            ->assertJsonMissingPath('data.commission_amount')
            ->assertJsonMissingPath('data.provider_net_amount');

        $lab = $this->createProvider(ProviderType::Lab);
        $labOrder = LabOrder::query()->create([
            'order_number' => 'LAB-CONTRACT-'.Str::upper(Str::random(6)),
            'patient_user_id' => $patient->id,
            'lab_provider_id' => $lab->id,
            'subtotal' => 200,
            'discount_total' => 0,
            'commission_amount' => 20,
            'provider_net_amount' => 180,
            'grand_total' => 200,
            'currency' => 'EGP',
            'payment_status' => LabOrderPaymentStatus::Unpaid,
            'order_status' => LabOrderStatus::LabReview,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit,
        ]);

        $this->getJson('/api/v1/lab/orders/'.$labOrder->id)
            ->assertOk()
            ->assertJsonPath('data.grand_total', '200.00')
            ->assertJsonMissingPath('data.commission_amount')
            ->assertJsonMissingPath('data.provider_net_amount');

        $conversation = AiConversation::query()->create([
            'patient_user_id' => $patient->id,
            'title' => 'Contract AI',
            'status' => AiConversationStatus::Active,
            'provider' => AiProvider::Fake,
            'language' => AiLanguage::Arabic,
            'context_enabled' => true,
            'safety_level' => AiSafetyLevel::Standard,
        ]);
        AiMessage::query()->create([
            'conversation_id' => $conversation->id,
            'patient_user_id' => $patient->id,
            'role' => AiMessageRole::Assistant,
            'content' => 'Safe contract response.',
            'safety_classification' => AiSafetyClassification::Safe,
            'was_refused' => false,
            'provider' => AiProvider::Fake,
            'metadata' => [
                'safe_note' => 'visible',
                'raw_response' => 'unsafe raw response',
                'api_key' => 'deepseek-secret',
                'nested' => ['authorization' => 'Bearer secret'],
            ],
        ]);

        $aiResponse = $this->getJson('/api/v1/ai/conversations/'.$conversation->id.'/messages')
            ->assertOk()
            ->assertJsonPath('data.0.metadata.safe_note', 'visible');

        $this->assertStringNotContainsString('unsafe raw response', $aiResponse->getContent());
        $this->assertStringNotContainsString('deepseek-secret', $aiResponse->getContent());
        $this->assertStringNotContainsString('Bearer secret', $aiResponse->getContent());

        $notification = app(NotificationService::class)->sendToUser(
            $patient,
            'system_notice',
            ['body' => 'Contract notification'],
            [
                'data' => [
                    'visible_id' => 55,
                    'api_key' => 'notification-secret',
                    'file_path' => 'I:\\private\\result.pdf',
                    'provider_net_amount' => 500,
                    'commission_amount' => 50,
                ],
                'idempotency_key' => 'contract-notification-privacy',
            ],
        );

        $notificationResponse = $this->getJson('/api/v1/notifications/'.$notification->id)
            ->assertOk()
            ->assertJsonPath('data.data.visible_id', 55);

        $this->assertStringNotContainsString('notification-secret', $notificationResponse->getContent());
        $this->assertStringNotContainsString('private\\result.pdf', $notificationResponse->getContent());
        $this->assertStringNotContainsString('provider_net_amount', $notificationResponse->getContent());
        $this->assertStringNotContainsString('commission_amount', $notificationResponse->getContent());

        $prescriptionId = $this->post('/api/v1/pharmacy/prescriptions', [
            'pharmacy_provider_id' => $pharmacy->id,
            'file' => UploadedFile::fake()->create('prescription.pdf', 100, 'application/pdf'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.file.visibility', 'private')
            ->assertJsonMissingPath('data.file.path')
            ->assertJsonMissingPath('data.file.url')
            ->json('data.id');

        $prescription = PharmacyPrescription::query()->with('uploadedFile')->findOrFail($prescriptionId);
        Storage::disk('medical_private')->assertExists($prescription->uploadedFile->path);
    }

    public function test_private_file_download_contract_blocks_unauthorized_access(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser('contract-file-owner@example.com');
        $otherPatient = $this->patientUser('contract-file-other@example.com');
        $pharmacy = $this->createProvider(ProviderType::Pharmacy);

        Sanctum::actingAs($patient);
        $prescriptionId = $this->post('/api/v1/pharmacy/prescriptions', [
            'pharmacy_provider_id' => $pharmacy->id,
            'file' => UploadedFile::fake()->create('prescription.pdf', 100, 'application/pdf'),
        ])
            ->assertCreated()
            ->assertJsonMissingPath('data.file.path')
            ->json('data.id');

        $prescription = PharmacyPrescription::query()->with('uploadedFile')->findOrFail($prescriptionId);

        $this->get('/api/v1/pharmacy/prescriptions/'.$prescriptionId.'/download')
            ->assertOk();

        $this->assertContains($this->get('/storage/'.$prescription->uploadedFile->path)->status(), [403, 404]);

        Sanctum::actingAs($otherPatient);
        $this->getJson('/api/v1/pharmacy/prescriptions/'.$prescriptionId.'/download')
            ->assertForbidden()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
    }

    public function test_system_endpoints_docs_and_mvp_routes_are_contract_ready(): void
    {
        $this->getJson('/api/v1/system/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonMissingPath('data.database_password')
            ->assertJsonMissingPath('data.app_key');

        $this->getJson('/api/v1/system/readiness')
            ->assertUnauthorized()
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);

        Sanctum::actingAs($this->adminUser());
        $this->getJson('/api/v1/system/readiness')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.checks.database.status', 'ok');

        $this->assertFileExists(base_path('docs/api/API_ROUTE_INVENTORY.md'));
        $this->assertFileExists(base_path('docs/api/STANDARD_RESPONSE_CONTRACT.md'));
        $this->assertFileExists(base_path('docs/api/PAGINATION_CONTRACT.md'));
        $this->assertFileExists(base_path('docs/api/AUTH_SESSION_CONTRACT.md'));
        $this->assertFileExists(base_path('docs/api/STATUS_ENUMS_FOR_MOBILE.md'));
        $this->assertFileExists(base_path('docs/api/FILE_UPLOAD_DOWNLOAD_CONTRACT.md'));
        $this->assertFileExists(base_path('docs/api/openapi-mobile-mvp.yaml'));
        $this->assertFileExists(base_path('docs/mobile/FLUTTER_INTEGRATION_READINESS.md'));

        $snapshot = json_decode((string) file_get_contents(base_path('docs/api/route-snapshot.json')), true);
        $this->assertIsArray($snapshot);
        $this->assertNotEmpty($snapshot);

        foreach ([
            ['POST', '/api/v1/auth/login'],
            ['GET', '/api/v1/doctors'],
            ['GET', '/api/v1/doctors/1/slots'],
            ['POST', '/api/v1/appointments'],
            ['GET', '/api/v1/payment-methods'],
            ['GET', '/api/v1/pharmacies/1/products'],
            ['POST', '/api/v1/pharmacy/orders'],
            ['POST', '/api/v1/lab/orders'],
            ['GET', '/api/v1/health/vitals'],
            ['POST', '/api/v1/medications/reminders'],
            ['POST', '/api/v1/care-plans'],
            ['POST', '/api/v1/ai/ask'],
            ['GET', '/api/v1/notifications'],
            ['GET', '/api/v1/system/health'],
        ] as [$method, $uri]) {
            $this->assertRouteExists($method, $uri);
        }
    }

    private function assertRouteExists(string $method, string $uri): void
    {
        $route = Route::getRoutes()->match(Request::create($uri, $method));

        $this->assertNotNull($route, $method.' '.$uri.' route is missing.');
    }

    private function patientUser(string $email): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'contract-admin-'.Str::random(8).'@example.com']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    private function createProvider(ProviderType $type, array $overrides = []): Provider
    {
        return Provider::query()->create([
            'type' => $type,
            'owner_user_id' => User::factory()->create()->id,
            'name_en' => Str::headline($type->value).' Provider '.Str::random(6),
            'slug' => $type->value.'-contract-'.Str::lower(Str::random(8)),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
            ...$overrides,
        ]);
    }
}
