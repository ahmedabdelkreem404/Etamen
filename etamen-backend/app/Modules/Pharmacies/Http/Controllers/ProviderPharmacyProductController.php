<?php

namespace App\Modules\Pharmacies\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Pharmacies\Application\Services\PharmacyAccessService;
use App\Modules\Pharmacies\Application\Services\PharmacyProductService;
use App\Modules\Pharmacies\Http\Requests\PharmacyProductRequest;
use App\Modules\Pharmacies\Http\Resources\PharmacyProductResource;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use Illuminate\Http\Request;

class ProviderPharmacyProductController extends ApiController
{
    public function __construct(
        private readonly PharmacyAccessService $accessService,
        private readonly PharmacyProductService $productService,
    ) {}

    public function index(Request $request)
    {
        $provider = $this->accessService->currentPharmacyFor($request->user());

        $products = PharmacyProduct::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('id')
            ->get();

        return $this->success(PharmacyProductResource::collection($products), 'Pharmacy products.');
    }

    public function store(PharmacyProductRequest $request)
    {
        $product = $this->productService->createForCurrentPharmacy($request->user(), $request->validated());

        return $this->success(new PharmacyProductResource($product), 'Pharmacy product created.', 201);
    }

    public function show(Request $request, PharmacyProduct $product)
    {
        $this->authorize('view', $product);

        return $this->success(new PharmacyProductResource($product), 'Pharmacy product details.');
    }

    public function update(PharmacyProductRequest $request, PharmacyProduct $product)
    {
        $this->authorize('update', $product);
        $product = $this->productService->update($request->user(), $product, $request->validated());

        return $this->success(new PharmacyProductResource($product), 'Pharmacy product updated.');
    }

    public function destroy(Request $request, PharmacyProduct $product)
    {
        $this->authorize('delete', $product);
        $product = $this->productService->deactivate($request->user(), $product);

        return $this->success(new PharmacyProductResource($product), 'Pharmacy product deactivated.');
    }
}
