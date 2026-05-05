<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Notifications\Application\Services\NotificationTemplateService;
use App\Modules\Notifications\Http\Requests\NotificationTemplateRequest;
use App\Modules\Notifications\Http\Resources\NotificationDispatchResource;
use App\Modules\Notifications\Http\Resources\NotificationResource;
use App\Modules\Notifications\Http\Resources\NotificationTemplateResource;
use App\Modules\Notifications\Http\Resources\NotificationTokenResource;
use App\Modules\Notifications\Http\Resources\SchedulerRunResource;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;
use App\Modules\Notifications\Infrastructure\Models\NotificationTemplate;
use App\Modules\Notifications\Infrastructure\Models\NotificationToken;
use App\Modules\Notifications\Infrastructure\Models\SchedulerRun;
use Illuminate\Http\Request;

class AdminNotificationController extends ApiController
{
    public function __construct(private readonly NotificationTemplateService $templates) {}

    public function notifications(Request $request)
    {
        return $this->success(NotificationResource::collection(Notification::query()->latest('id')->paginate($this->perPage($request, 50))), 'Notifications.');
    }

    public function dispatches(Request $request)
    {
        $query = NotificationDispatch::query()->latest('id');

        foreach (['status', 'channel', 'category'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, (string) $request->string($filter));
            }
        }

        return $this->success(NotificationDispatchResource::collection($query->paginate($this->perPage($request, 50))), 'Notification dispatches.');
    }

    public function templates(Request $request)
    {
        return $this->success(NotificationTemplateResource::collection(NotificationTemplate::query()->orderBy('key')->limit($this->perPage($request, 100))->get()), 'Notification templates.');
    }

    public function storeTemplate(NotificationTemplateRequest $request)
    {
        return $this->success(new NotificationTemplateResource($this->templates->create($request->user(), $request->validated())), 'Notification template created.', 201);
    }

    public function updateTemplate(NotificationTemplateRequest $request, NotificationTemplate $template)
    {
        return $this->success(new NotificationTemplateResource($this->templates->update($request->user(), $template, $request->validated())), 'Notification template updated.');
    }

    public function schedulerRuns(Request $request)
    {
        return $this->success(SchedulerRunResource::collection(SchedulerRun::query()->latest('id')->paginate($this->perPage($request, 50))), 'Scheduler runs.');
    }

    public function tokens(Request $request)
    {
        return $this->success(NotificationTokenResource::collection(NotificationToken::query()->latest('id')->paginate($this->perPage($request, 50))), 'Notification tokens.');
    }
}
