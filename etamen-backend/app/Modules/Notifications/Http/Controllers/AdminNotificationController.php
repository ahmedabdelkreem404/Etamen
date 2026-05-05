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

    public function notifications()
    {
        return $this->success(NotificationResource::collection(Notification::query()->latest('id')->paginate(50)), 'Notifications.');
    }

    public function dispatches(Request $request)
    {
        $query = NotificationDispatch::query()->latest('id');

        foreach (['status', 'channel', 'category'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, (string) $request->string($filter));
            }
        }

        return $this->success(NotificationDispatchResource::collection($query->paginate(50)), 'Notification dispatches.');
    }

    public function templates()
    {
        return $this->success(NotificationTemplateResource::collection(NotificationTemplate::query()->orderBy('key')->get()), 'Notification templates.');
    }

    public function storeTemplate(NotificationTemplateRequest $request)
    {
        return $this->success(new NotificationTemplateResource($this->templates->create($request->user(), $request->validated())), 'Notification template created.', 201);
    }

    public function updateTemplate(NotificationTemplateRequest $request, NotificationTemplate $template)
    {
        return $this->success(new NotificationTemplateResource($this->templates->update($request->user(), $template, $request->validated())), 'Notification template updated.');
    }

    public function schedulerRuns()
    {
        return $this->success(SchedulerRunResource::collection(SchedulerRun::query()->latest('id')->paginate(50)), 'Scheduler runs.');
    }

    public function tokens()
    {
        return $this->success(NotificationTokenResource::collection(NotificationToken::query()->latest('id')->paginate(50)), 'Notification tokens.');
    }
}
