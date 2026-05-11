<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Application\Services\LabAccessService;
use App\Modules\Labs\Http\Resources\LabPackageResource;
use App\Modules\Labs\Http\Resources\LabTestResource;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicLabCatalogController extends ApiController
{
    public function __construct(private readonly LabAccessService $accessService) {}

    public function tests(Request $request, Provider $lab)
    {
        $lab = $this->accessService->publicLab($lab->id);
        $filters = $this->validatedFilters($request);

        $tests = LabTest::query()
            ->where('provider_id', $lab->id)
            ->where('is_active', true)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name_ar', 'like', '%'.$search.'%')
                        ->orWhere('name_en', 'like', '%'.$search.'%')
                        ->orWhere('description_ar', 'like', '%'.$search.'%')
                        ->orWhere('description_en', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['sample_type'] ?? null, fn ($query, string $sampleType) => $query->where('sample_type', $sampleType))
            ->when($filters['result_time_max_hours'] ?? null, fn ($query, int|string $hours) => $query->where('result_time_hours', '<=', $hours))
            ->when($filters['min_price'] ?? null, fn ($query, string|int|float $price) => $query->where('price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn ($query, string|int|float $price) => $query->where('price', '<=', $price));

        $this->sortTests($tests, $filters['sort'] ?? 'name');

        $tests = $tests
            ->limit($this->perPage($request))
            ->get();

        return $this->success(LabTestResource::collection($tests), 'Lab tests.');
    }

    public function packages(Request $request, Provider $lab)
    {
        $lab = $this->accessService->publicLab($lab->id);
        $filters = $this->validatedFilters($request);

        $packages = LabPackage::query()
            ->where('provider_id', $lab->id)
            ->where('is_active', true)
            ->with('tests')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name_ar', 'like', '%'.$search.'%')
                        ->orWhere('name_en', 'like', '%'.$search.'%')
                        ->orWhere('description_ar', 'like', '%'.$search.'%')
                        ->orWhere('description_en', 'like', '%'.$search.'%')
                        ->orWhereHas('tests', function ($query) use ($search): void {
                            $query->where('name_ar', 'like', '%'.$search.'%')
                                ->orWhere('name_en', 'like', '%'.$search.'%')
                                ->orWhere('code', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($filters['sample_type'] ?? null, fn ($query, string $sampleType) => $query->whereHas('tests', fn ($query) => $query->where('sample_type', $sampleType)))
            ->when($filters['result_time_max_hours'] ?? null, fn ($query, int|string $hours) => $query->whereHas('tests', fn ($query) => $query->where('result_time_hours', '<=', $hours)))
            ->when($filters['min_price'] ?? null, fn ($query, string|int|float $price) => $query->where('price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn ($query, string|int|float $price) => $query->where('price', '<=', $price));

        $this->sortPackages($packages, $filters['sort'] ?? 'name');

        $packages = $packages
            ->limit($this->perPage($request))
            ->get();

        return $this->success(LabPackageResource::collection($packages), 'Lab packages.');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'sample_type' => ['nullable', 'string', 'max:80'],
            'result_time_max_hours' => ['nullable', 'integer', 'min:1'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'name', 'result_time'])],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function sortTests($query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->latest(),
            'price_asc' => $query->orderBy('price')->orderBy('name_en'),
            'price_desc' => $query->orderByDesc('price')->orderBy('name_en'),
            'result_time' => $query->orderBy('result_time_hours')->orderBy('name_en'),
            default => $query->orderBy('name_en')->orderBy('id'),
        };
    }

    private function sortPackages($query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->latest(),
            'price_asc' => $query->orderBy('price')->orderBy('name_en'),
            'price_desc' => $query->orderByDesc('price')->orderBy('name_en'),
            'result_time' => $query->withMin('tests', 'result_time_hours')->orderBy('tests_min_result_time_hours')->orderBy('name_en'),
            default => $query->orderBy('name_en')->orderBy('id'),
        };
    }
}
