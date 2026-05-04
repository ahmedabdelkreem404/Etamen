<?php

namespace App\Modules\Appointments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Appointments\Application\Services\DoctorBookingContextService;
use App\Modules\Appointments\Application\Services\DoctorScheduleService;
use App\Modules\Appointments\Application\Services\GenerateDoctorSlots;
use App\Modules\Appointments\Http\Requests\GenerateDoctorSlotsRequest;
use App\Modules\Appointments\Http\Requests\StoreDoctorHolidayRequest;
use App\Modules\Appointments\Http\Requests\StoreDoctorScheduleDayRequest;
use App\Modules\Appointments\Http\Requests\StoreDoctorScheduleRequest;
use App\Modules\Appointments\Http\Requests\UpdateDoctorScheduleDayRequest;
use App\Modules\Appointments\Http\Requests\UpdateDoctorScheduleRequest;
use App\Modules\Appointments\Http\Resources\DoctorHolidayResource;
use App\Modules\Appointments\Http\Resources\DoctorScheduleDayResource;
use App\Modules\Appointments\Http\Resources\DoctorScheduleResource;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;
use App\Modules\Appointments\Infrastructure\Models\DoctorScheduleDay;
use Illuminate\Http\Request;

class ProviderDoctorScheduleController extends ApiController
{
    public function __construct(
        private readonly DoctorBookingContextService $contextService,
        private readonly DoctorScheduleService $scheduleService,
        private readonly GenerateDoctorSlots $generateDoctorSlots,
    ) {}

    public function index(Request $request)
    {
        $doctor = $this->contextService->doctorForUser($request->user());

        return $this->success(
            DoctorScheduleResource::collection($doctor->schedules()->with('days')->orderByDesc('id')->get()),
            'Doctor schedules.',
        );
    }

    public function store(StoreDoctorScheduleRequest $request)
    {
        $schedule = $this->scheduleService->createSchedule($request->user(), $request->validated());

        return $this->success(new DoctorScheduleResource($schedule->load('days')), 'Doctor schedule created.', 201);
    }

    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $schedule = $this->scheduleService->updateSchedule($request->user(), $schedule, $request->validated());

        return $this->success(new DoctorScheduleResource($schedule->load('days')), 'Doctor schedule updated.');
    }

    public function addDay(StoreDoctorScheduleDayRequest $request, DoctorSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $day = $this->scheduleService->addScheduleDay($request->user(), $schedule, $request->validated());

        return $this->success(new DoctorScheduleDayResource($day), 'Doctor schedule day created.', 201);
    }

    public function updateDay(UpdateDoctorScheduleDayRequest $request, DoctorScheduleDay $day)
    {
        $this->authorize('update', $day);

        $day = $this->scheduleService->updateScheduleDay($request->user(), $day, $request->validated());

        return $this->success(new DoctorScheduleDayResource($day), 'Doctor schedule day updated.');
    }

    public function holidays(Request $request)
    {
        $doctor = $this->contextService->doctorForUser($request->user());

        return $this->success(
            DoctorHolidayResource::collection($doctor->holidays()->orderByDesc('starts_at')->get()),
            'Doctor holidays.',
        );
    }

    public function storeHoliday(StoreDoctorHolidayRequest $request)
    {
        $holiday = $this->scheduleService->createHoliday($request->user(), $request->validated());

        return $this->success(new DoctorHolidayResource($holiday), 'Doctor holiday created.', 201);
    }

    public function generateSlots(GenerateDoctorSlotsRequest $request)
    {
        $result = $this->generateDoctorSlots->generate($request->user(), $request->validated());

        return $this->success($result, 'Doctor slots generated.');
    }
}
