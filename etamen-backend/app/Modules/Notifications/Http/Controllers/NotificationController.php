<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Notifications\Application\Services\NotificationService;
use App\Modules\Notifications\Http\Resources\NotificationResource;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request)
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(30);

        return $this->success(NotificationResource::collection($notifications), 'Notifications.');
    }

    public function unreadCount(Request $request)
    {
        $count = Notification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return $this->success(['unread_count' => $count], 'Unread notification count.');
    }

    public function show(Notification $notification)
    {
        $this->authorize('view', $notification);

        return $this->success(new NotificationResource($notification), 'Notification details.');
    }

    public function read(Request $request, Notification $notification)
    {
        $this->authorize('update', $notification);

        return $this->success(new NotificationResource($this->notifications->markRead($request->user(), $notification)), 'Notification marked read.');
    }

    public function readAll(Request $request)
    {
        $count = $this->notifications->markAllRead($request->user());

        return $this->success(['updated_count' => $count], 'Notifications marked read.');
    }

    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);
        $notification->delete();

        return $this->success(null, 'Notification deleted.');
    }
}
