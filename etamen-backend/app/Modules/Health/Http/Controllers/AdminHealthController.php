<?php

namespace App\Modules\Health\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Health\Application\Services\HealthAccessLogService;
use App\Modules\Health\Http\Resources\HealthAccessLogResource;
use App\Modules\Health\Http\Resources\HealthProfileResource;
use App\Modules\Health\Http\Resources\VitalRecordResource;
use App\Modules\Health\Infrastructure\Models\HealthAccessLog;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use Illuminate\Http\Request;

class AdminHealthController extends ApiController
{
    public function __construct(private readonly HealthAccessLogService $accessLogs) {}

    public function profiles(Request $request)
    {
        $profiles = HealthProfile::query()
            ->with('patient')
            ->when($request->query('patient_user_id'), fn ($query, $patientId) => $query->where('patient_user_id', $patientId))
            ->orderByDesc('id')
            ->get();

        return $this->success(HealthProfileResource::collection($profiles), 'Health profiles.');
    }

    public function profile(Request $request, HealthProfile $profile)
    {
        $this->accessLogs->logRead($profile->patient_user_id, $request->user(), 'admin.health_profile.viewed', $profile, request: $request);

        return $this->success(new HealthProfileResource($profile), 'Health profile details.');
    }

    public function vitals(Request $request)
    {
        $records = VitalRecord::query()
            ->when($request->query('patient_user_id'), fn ($query, $patientId) => $query->where('patient_user_id', $patientId))
            ->when($request->query('vital_type'), fn ($query, $type) => $query->where('vital_type', $type))
            ->when($request->query('from'), fn ($query, $date) => $query->whereDate('measured_at', '>=', $date))
            ->when($request->query('to'), fn ($query, $date) => $query->whereDate('measured_at', '<=', $date))
            ->orderByDesc('measured_at')
            ->get();

        return $this->success(VitalRecordResource::collection($records), 'Vital records.');
    }

    public function vital(Request $request, VitalRecord $vital)
    {
        $this->accessLogs->logRead($vital->patient_user_id, $request->user(), 'admin.vital_record.viewed', $vital, request: $request);

        return $this->success(new VitalRecordResource($vital), 'Vital record details.');
    }

    public function accessLogs()
    {
        return $this->success(
            HealthAccessLogResource::collection(HealthAccessLog::query()->orderByDesc('id')->get()),
            'Health access logs.',
        );
    }
}
