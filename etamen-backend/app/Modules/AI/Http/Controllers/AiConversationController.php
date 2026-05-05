<?php

namespace App\Modules\AI\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AI\Application\Services\AiConversationService;
use App\Modules\AI\Http\Requests\AiConversationRequest;
use App\Modules\AI\Http\Resources\AiConversationResource;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use Illuminate\Http\Request;

class AiConversationController extends ApiController
{
    public function __construct(private readonly AiConversationService $conversations) {}

    public function index(Request $request)
    {
        $conversations = AiConversation::query()
            ->where('patient_user_id', $request->user()->id)
            ->withCount('messages')
            ->latest('last_message_at')
            ->latest('id')
            ->limit($this->perPage($request, 20))
            ->get();

        return $this->success(AiConversationResource::collection($conversations), 'AI conversations.');
    }

    public function store(AiConversationRequest $request)
    {
        $conversation = $this->conversations->create($request->user(), $request->validated());

        return $this->success(new AiConversationResource($conversation), 'AI conversation created.', 201);
    }

    public function show(AiConversation $conversation)
    {
        $this->authorize('view', $conversation);

        return $this->success(new AiConversationResource($conversation->loadCount('messages')), 'AI conversation details.');
    }

    public function update(AiConversationRequest $request, AiConversation $conversation)
    {
        $this->authorize('update', $conversation);

        return $this->success(new AiConversationResource($this->conversations->update($request->user(), $conversation, $request->validated())), 'AI conversation updated.');
    }

    public function destroy(Request $request, AiConversation $conversation)
    {
        $this->authorize('delete', $conversation);

        return $this->success(new AiConversationResource($this->conversations->archive($request->user(), $conversation)), 'AI conversation archived.');
    }
}
