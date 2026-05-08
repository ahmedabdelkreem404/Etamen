<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Http\Resources\ProviderResource;
use App\Modules\Providers\Http\Resources\SpecialtyResource;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\Specialty;
use Illuminate\Http\Request;

class PublicProviderController extends ApiController
{
    public function doctors(Request $request)
    {
        return $this->success($this->publicProviders($request, ProviderType::Doctor), 'Approved doctors.');
    }

    public function doctor(Provider $doctor)
    {
        return $this->showPublicProvider($doctor, ProviderType::Doctor, 'Doctor details.');
    }

    public function pharmacies(Request $request)
    {
        return $this->success($this->publicProviders($request, ProviderType::Pharmacy), 'Approved pharmacies.');
    }

    public function pharmacy(Provider $pharmacy)
    {
        return $this->showPublicProvider($pharmacy, ProviderType::Pharmacy, 'Pharmacy details.');
    }

    public function labs(Request $request)
    {
        return $this->success($this->publicProviders($request, ProviderType::Lab), 'Approved labs.');
    }

    public function lab(Provider $lab)
    {
        return $this->showPublicProvider($lab, ProviderType::Lab, 'Lab details.');
    }

    public function specialties(Request $request)
    {
        return $this->success(
            SpecialtyResource::collection(Specialty::query()->where('is_active', true)->orderBy('name_en')->limit($this->perPage($request, 100))->get()),
            'Active specialties.',
        );
    }

    private function publicProviders(Request $request, ProviderType $type)
    {
        return ProviderResource::collection(
            Provider::query()
                ->publiclyVisible()
                ->publicDiscoveryEnabled()
                ->where('type', $type)
                ->with($this->publicProviderRelations())
                ->orderBy('name_en')
                ->limit($this->perPage($request))
                ->get(),
        );
    }

    private function showPublicProvider(Provider $provider, ProviderType $type, string $message)
    {
        abort_if($provider->type !== $type || ! $provider->is_active || $provider->status->value !== 'approved', 404);

        return $this->success(
            new ProviderResource($provider->load($this->publicProviderRelations())),
            $message,
        );
    }

    private function publicProviderRelations(): array
    {
        return [
            'doctorProfile' => fn ($query) => $query
                ->with('specialties')
                ->withAvg(['reviews as rating_average' => fn ($reviewQuery) => $reviewQuery->where('is_visible', true)], 'rating')
                ->withCount(['reviews as reviews_count' => fn ($reviewQuery) => $reviewQuery->where('is_visible', true)]),
            'pharmacyProfile',
            'labProfile',
            'bookingSettings',
            'activeContract',
            'publicDocuments.file',
            'publicServices.category',
            'branches.city',
            'branches.area',
        ];
    }
}
