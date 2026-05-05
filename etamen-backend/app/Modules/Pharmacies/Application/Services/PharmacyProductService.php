<?php

namespace App\Modules\Pharmacies\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use Illuminate\Support\Facades\DB;

class PharmacyProductService
{
    public function __construct(
        private readonly PharmacyAccessService $accessService,
        private readonly FileStorageService $fileStorageService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function createForCurrentPharmacy(User $user, array $data): PharmacyProduct
    {
        return DB::transaction(function () use ($user, $data): PharmacyProduct {
            $provider = $this->accessService->currentPharmacyFor($user);

            $productData = $this->productData($data);
            if (isset($data['image'])) {
                $uploadedImage = $this->fileStorageService->storePrivate(
                    $data['image'],
                    FileCategory::PharmacyProductImage,
                    $user,
                    $provider,
                    ['provider_id' => $provider->id],
                );
                $productData['image_file_id'] = $uploadedImage->id;
            }

            $product = PharmacyProduct::query()->create([
                'provider_id' => $provider->id,
                ...$productData,
            ]);

            $this->auditLogService->log('pharmacy_product.created', $product, $user, metadata: [
                'provider_id' => $provider->id,
            ]);

            return $product->refresh()->load('provider');
        });
    }

    public function update(User $user, PharmacyProduct $product, array $data): PharmacyProduct
    {
        return DB::transaction(function () use ($user, $product, $data): PharmacyProduct {
            $provider = $this->accessService->currentPharmacyFor($user);
            abort_if((int) $product->provider_id !== (int) $provider->id, 403);

            $before = $product->getAttributes();
            $productData = $this->productData($data, partial: true);
            if (isset($data['image'])) {
                $uploadedImage = $this->fileStorageService->storePrivate(
                    $data['image'],
                    FileCategory::PharmacyProductImage,
                    $user,
                    $provider,
                    ['provider_id' => $provider->id, 'product_id' => $product->id],
                );
                $productData['image_file_id'] = $uploadedImage->id;
            }

            $product->update($productData);

            $this->auditLogService->log('pharmacy_product.updated', $product, $user, before: $before, after: $product->getAttributes());

            return $product->refresh()->load('provider');
        });
    }

    public function deactivate(User $user, PharmacyProduct $product): PharmacyProduct
    {
        return DB::transaction(function () use ($user, $product): PharmacyProduct {
            $provider = $this->accessService->currentPharmacyFor($user);
            abort_if((int) $product->provider_id !== (int) $provider->id, 403);

            $before = $product->getAttributes();
            $product->update(['is_active' => false]);

            $this->auditLogService->log('pharmacy_product.deactivated', $product, $user, before: $before, after: $product->getAttributes());

            return $product->refresh();
        });
    }

    private function productData(array $data, bool $partial = false): array
    {
        $allowed = collect($data)->only([
            'name_ar',
            'name_en',
            'description_ar',
            'description_en',
            'sku',
            'price',
            'requires_prescription',
            'stock_quantity',
            'is_active',
            'metadata',
        ])->all();

        if (! $partial) {
            $allowed['is_active'] = $allowed['is_active'] ?? true;
            $allowed['requires_prescription'] = $allowed['requires_prescription'] ?? false;
            $allowed['stock_quantity'] = $allowed['stock_quantity'] ?? 0;
        }

        return $allowed;
    }
}
