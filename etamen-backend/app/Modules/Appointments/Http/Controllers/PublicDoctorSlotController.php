<?php

namespace App\Modules\Appointments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Http\Resources\AppointmentSlotResource;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;

class PublicDoctorSlotController extends ApiController
{
    public function index(Request $request, Provider $doctor)
    {
        abort_if(
            $doctor->type !== ProviderType::Doctor
            || $doctor->status !== ProviderStatus::Approved
            || ! $doctor->is_active
            || ! $doctor->doctorProfile,
            404,
        );

        $slots = $doctor->doctorProfile
            ->slots()
            ->where('status', AppointmentSlotStatus::Available)
            ->where('starts_at', '>', now())
            ->when($request->query('start_date'), fn ($query, $date) => $query->whereDate('starts_at', '>=', $date))
            ->when($request->query('end_date'), fn ($query, $date) => $query->whereDate('starts_at', '<=', $date))
            ->orderBy('starts_at')
            ->get();

        return $this->success(AppointmentSlotResource::collection($slots), 'Available doctor slots.');
    }
}
