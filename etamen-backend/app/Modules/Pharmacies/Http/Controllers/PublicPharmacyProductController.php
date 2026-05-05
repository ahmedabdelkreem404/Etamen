<?php

namespace App\Modules\Pharmacies\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Pharmacies\Application\Services\PharmacyAccessService;
use App\Modules\Pharmacies\Http\Resources\PharmacyProductResource;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;

class PublicPharmacyProductController extends ApiController
{
    public function __construct(private readonly PharmacyAccessService $accessService) {}

    public function index(Request $request, Provider $pharmacy)
    {
        $pharmacy = $this->accessService->publicPharmacy($pharmacy->id);

        $products = PharmacyProduct::query()
            ->where('provider_id', $pharmacy->id)
            ->active()
            ->orderBy('name_en')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(PharmacyProductResource::collection($products), 'Active pharmacy products.');
    }
}
