<?php

namespace App\Modules\AI\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AI\Application\Services\AiAssistantService;
use App\Modules\AI\Http\Requests\AiMessageRequest;
use App\Modules\AI\Http\Resources\AiMessageResource;
use App\Modules\AI\Infrastructure\Models\AiConversation;

class AiMessageController extends ApiController
{
    public function __construct(private readonly AiAssistantService $assistant) {}

    public function index(AiConversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()->oldest('id')->get();

        return $this->success(AiMessageResource::collection($messages), 'AI messages.');
    }

    public function store(AiMessageRequest $request, AiConversation $conversation)
    {
        $this->authorize('update', $conversation);
        $message = $this->assistant->send($request->user(), $conversation, $request->validated());

        return $this->success(new AiMessageResource($message), 'AI assistant response created.', 201);
    }
}
