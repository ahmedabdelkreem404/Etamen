<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Http\Resources\ProviderResource;
use App\Modules\Providers\Http\Resources\SpecialtyResource;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\Specialty;

class PublicProviderController extends ApiController
{
    public function doctors()
    {
        return $this->success($this->publicProviders(ProviderType::Doctor), 'Approved doctors.');
    }

    public function doctor(Provider $doctor)
    {
        return $this->showPublicProvider($doctor, ProviderType::Doctor, 'Doctor details.');
    }

    public function pharmacies()
    {
        return $this->success($this->publicProviders(ProviderType::Pharmacy), 'Approved pharmacies.');
    }

    public function pharmacy(Provider $pharmacy)
    {
        return $this->showPublicProvider($pharmacy, ProviderType::Pharmacy, 'Pharmacy details.');
    }

    public function labs()
    {
        return $this->success($this->publicProviders(ProviderType::Lab), 'Approved labs.');
    }

    public function lab(Provider $lab)
    {
        return $this->showPublicProvider($lab, ProviderType::Lab, 'Lab details.');
    }

    public function specialties()
    {
        return $this->success(
            SpecialtyResource::collection(Specialty::query()->where('is_active', true)->orderBy('name_en')->get()),
            'Active specialties.',
        );
    }

    private function publicProviders(ProviderType $type)
    {
        return ProviderResource::collection(
            Provider::query()
                ->publiclyVisible()
                ->where('type', $type)
                ->with(['doctorProfile.specialties', 'pharmacyProfile', 'labProfile', 'branches.city', 'branches.area'])
                ->orderBy('name_en')
                ->get(),
        );
    }

    private function showPublicProvider(Provider $provider, ProviderType $type, string $message)
    {
        abort_if($provider->type !== $type || ! $provider->is_active || $provider->status->value !== 'approved', 404);

        return $this->success(
            new ProviderResource($provider->load(['doctorProfile.specialties', 'pharmacyProfile', 'labProfile', 'branches.city', 'branches.area'])),
            $message,
        );
    }
}
