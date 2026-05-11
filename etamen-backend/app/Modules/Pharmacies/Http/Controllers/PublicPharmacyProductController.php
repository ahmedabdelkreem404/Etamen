<?php

namespace App\Modules\Pharmacies\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Pharmacies\Application\Services\PharmacyAccessService;
use App\Modules\Pharmacies\Http\Resources\PharmacyProductResource;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicPharmacyProductController extends ApiController
{
    public function __construct(private readonly PharmacyAccessService $accessService) {}

    public function index(Request $request, Provider $pharmacy)
    {
        $pharmacy = $this->accessService->publicPharmacy($pharmacy->id);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
            'requires_prescription' => ['nullable', 'boolean'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'name'])],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $products = PharmacyProduct::query()
            ->where('provider_id', $pharmacy->id)
            ->active()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name_ar', 'like', '%'.$search.'%')
                        ->orWhere('name_en', 'like', '%'.$search.'%')
                        ->orWhere('description_ar', 'like', '%'.$search.'%')
                        ->orWhere('description_en', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['category'] ?? null, function ($query, string $category): void {
                $query->where(function ($query) use ($category): void {
                    $query->where('metadata->category', $category)
                        ->orWhere('metadata->category_ar', $category)
                        ->orWhere('metadata->category_en', $category);
                });
            })
            ->when(array_key_exists('requires_prescription', $filters), fn ($query) => $query->where('requires_prescription', $request->boolean('requires_prescription')))
            ->when($filters['min_price'] ?? null, fn ($query, string|int|float $price) => $query->where('price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn ($query, string|int|float $price) => $query->where('price', '<=', $price))
            ->when(array_key_exists('in_stock', $filters), function ($query) use ($request): void {
                $request->boolean('in_stock')
                    ? $query->where('stock_quantity', '>', 0)
                    : $query->where('stock_quantity', '<=', 0);
            });

        match ($filters['sort'] ?? 'name') {
            'newest' => $products->latest(),
            'price_asc' => $products->orderBy('price')->orderBy('name_en'),
            'price_desc' => $products->orderByDesc('price')->orderBy('name_en'),
            default => $products->orderBy('name_en')->orderBy('id'),
        };

        $products = $products
            ->limit($this->perPage($request))
            ->get();

        return $this->success(PharmacyProductResource::collection($products), 'Active pharmacy products.');
    }
}
