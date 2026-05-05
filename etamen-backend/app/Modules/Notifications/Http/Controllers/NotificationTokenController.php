<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Notifications\Application\Services\NotificationTokenService;
use App\Modules\Notifications\Http\Requests\NotificationTokenRequest;
use App\Modules\Notifications\Http\Resources\NotificationTokenResource;
use App\Modules\Notifications\Infrastructure\Models\NotificationToken;
use Illuminate\Http\Request;

class NotificationTokenController extends ApiController
{
    public function __construct(private readonly NotificationTokenService $tokens) {}

    public function index(Request $request)
    {
        return $this->success(
            NotificationTokenResource::collection(NotificationToken::query()->where('user_id', $request->user()->id)->latest('id')->get()),
            'Notification tokens.',
        );
    }

    public function store(NotificationTokenRequest $request)
    {
        return $this->success(
            new NotificationTokenResource($this->tokens->register($request->user(), $request->validated())),
            'Notification token registered.',
            201,
        );
    }

    public function destroy(Request $request, NotificationToken $token)
    {
        $this->authorize('delete', $token);
        $this->tokens->delete($request->user(), $token);

        return $this->success(null, 'Notification token deleted.');
    }
}
