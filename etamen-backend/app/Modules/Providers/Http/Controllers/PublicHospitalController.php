<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Http\Resources\HospitalDepartmentResource;
use App\Modules\Providers\Http\Resources\HospitalResource;
use App\Modules\Providers\Http\Resources\ProviderResource;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicHospitalController extends ApiController
{
    public function index(Request $request)
    {
        $hospitals = Provider::query()
            ->publiclyVisible()
            ->where('type', ProviderType::Hospital)
            ->with($this->hospitalRelations())
            ->withCount($this->hospitalCounts())
            ->orderBy('name_ar')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(HospitalResource::collection($hospitals), 'Approved hospitals.');
    }

    public function show(Provider $hospital)
    {
        $this->abortUnlessPublicHospital($hospital);

        return $this->success(
            new HospitalResource($hospital->load($this->hospitalRelations())->loadCount($this->hospitalCounts())),
            'Hospital details.',
        );
    }

    public function departments(Request $request, Provider $hospital)
    {
        $this->abortUnlessPublicHospital($hospital);

        $departments = HospitalDepartment::query()
            ->publiclyVisible()
            ->where('hospital_provider_id', $hospital->id)
            ->with('specialty')
            ->withCount([
                'doctors as doctors_count' => fn (Builder $query) => $query->publiclyVisible(),
            ])
            ->orderBy('name_ar')
            ->limit($this->perPage($request, 50))
            ->get();

        return $this->success(HospitalDepartmentResource::collection($departments), 'Hospital departments.');
    }

    public function doctors(Request $request, Provider $hospital)
    {
        $this->abortUnlessPublicHospital($hospital);

        return $this->success(
            ProviderResource::collection($this->hospitalDoctorsQuery($hospital)->limit($this->perPage($request))->get()),
            'Hospital doctors.',
        );
    }

    public function departmentDoctors(Request $request, Provider $hospital, HospitalDepartment $department)
    {
        $this->abortUnlessPublicHospital($hospital);
        $this->abortUnlessHospitalDepartment($hospital, $department);

        return $this->success(
            ProviderResource::collection(
                $this->hospitalDoctorsQuery($hospital)
                    ->whereHas('affiliatedHospitalLinks', fn (Builder $query) => $query
                        ->where('hospital_department_id', $department->id)
                        ->where('is_active', true))
                    ->limit($this->perPage($request))
                    ->get(),
            ),
            'Hospital department doctors.',
        );
    }

    private function hospitalDoctorsQuery(Provider $hospital): Builder
    {
        return Provider::query()
            ->publiclyVisible()
            ->where('type', ProviderType::Doctor)
            ->whereHas('affiliatedHospitalLinks', fn (Builder $query) => $query
                ->publiclyVisible()
                ->where('hospital_provider_id', $hospital->id))
            ->with($this->doctorRelations())
            ->orderBy('name_ar');
    }

    private function abortUnlessPublicHospital(Provider $hospital): void
    {
        abort_if(
            $hospital->type !== ProviderType::Hospital
            || ! $hospital->is_active
            || $hospital->status->value !== 'approved',
            404,
        );
    }

    private function abortUnlessHospitalDepartment(Provider $hospital, HospitalDepartment $department): void
    {
        abort_if(
            (int) $department->hospital_provider_id !== (int) $hospital->id
            || ! $department->is_active,
            404,
        );
    }

    private function hospitalRelations(): array
    {
        return [
            'hospitalProfile',
            'branches' => fn ($query) => $query->where('is_active', true)->with(['city', 'area']),
        ];
    }

    private function hospitalCounts(): array
    {
        return [
            'hospitalDepartments as departments_count' => fn (Builder $query) => $query->where('is_active', true),
            'hospitalDoctorLinks as doctors_count' => fn (Builder $query) => $query->publiclyVisible(),
        ];
    }

    private function doctorRelations(): array
    {
        return [
            'doctorProfile' => fn ($query) => $query
                ->with('specialties')
                ->withAvg(['reviews as rating_average' => fn ($reviewQuery) => $reviewQuery->where('is_visible', true)], 'rating')
                ->withCount(['reviews as reviews_count' => fn ($reviewQuery) => $reviewQuery->where('is_visible', true)]),
            'bookingSettings',
            'activeContract',
            'publicDocuments.file',
            'publicServices.category',
            'branches.city',
            'branches.area',
        ];
    }
}
