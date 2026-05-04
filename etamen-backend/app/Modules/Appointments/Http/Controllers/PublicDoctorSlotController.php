<?php

namespace App\Modules\Appointments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Http\Resources\AppointmentSlotResource;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Carbon\CarbonImmutable;
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

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        [$startDate, $endDate] = $this->resolvePublicDateRange($validated);
        $limit = min((int) ($validated['limit'] ?? 100), 100);

        $slots = $doctor->doctorProfile
            ->slots()
            ->where('status', AppointmentSlotStatus::Available)
            ->where('starts_at', '>=', $startDate)
            ->where('starts_at', '<=', $endDate)
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();

        return $this->success(AppointmentSlotResource::collection($slots), 'Available doctor slots.');
    }

    private function resolvePublicDateRange(array $data): array
    {
        $startDate = isset($data['start_date'])
            ? CarbonImmutable::parse($data['start_date'])->startOfDay()
            : now()->toImmutable();

        if ($startDate->lessThan(now())) {
            $startDate = now()->toImmutable();
        }

        $requestedEndDate = isset($data['end_date'])
            ? CarbonImmutable::parse($data['end_date'])->endOfDay()
            : $startDate->addDays(14)->endOfDay();

        $maxEndDate = $startDate->addDays(60)->endOfDay();
        $endDate = $requestedEndDate->lessThanOrEqualTo($maxEndDate)
            ? $requestedEndDate
            : $maxEndDate;

        return [$startDate, $endDate];
    }
}
