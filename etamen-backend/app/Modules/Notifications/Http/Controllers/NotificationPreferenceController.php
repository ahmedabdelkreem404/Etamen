<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Notifications\Application\Services\NotificationPreferenceService;
use App\Modules\Notifications\Http\Requests\NotificationPreferenceRequest;
use App\Modules\Notifications\Http\Resources\NotificationPreferenceResource;
use Illuminate\Http\Request;

class NotificationPreferenceController extends ApiController
{
    public function __construct(private readonly NotificationPreferenceService $preferences) {}

    public function index(Request $request)
    {
        return $this->success(NotificationPreferenceResource::collection($this->preferences->defaultsFor($request->user())), 'Notification preferences.');
    }

    public function update(NotificationPreferenceRequest $request)
    {
        return $this->success(
            NotificationPreferenceResource::collection($this->preferences->update($request->user(), $request->validated('preferences'))),
            'Notification preferences updated.',
        );
    }
}
