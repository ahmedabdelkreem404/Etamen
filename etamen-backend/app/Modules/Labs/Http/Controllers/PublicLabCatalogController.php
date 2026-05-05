<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Application\Services\LabAccessService;
use App\Modules\Labs\Http\Resources\LabPackageResource;
use App\Modules\Labs\Http\Resources\LabTestResource;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Providers\Infrastructure\Models\Provider;

class PublicLabCatalogController extends ApiController
{
    public function __construct(private readonly LabAccessService $accessService) {}

    public function tests(Provider $lab)
    {
        $lab = $this->accessService->publicLab($lab->id);
        $tests = LabTest::query()
            ->where('provider_id', $lab->id)
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get();

        return $this->success(LabTestResource::collection($tests), 'Lab tests.');
    }

    public function packages(Provider $lab)
    {
        $lab = $this->accessService->publicLab($lab->id);
        $packages = LabPackage::query()
            ->where('provider_id', $lab->id)
            ->where('is_active', true)
            ->with('tests')
            ->orderBy('name_en')
            ->get();

        return $this->success(LabPackageResource::collection($packages), 'Lab packages.');
    }
}
