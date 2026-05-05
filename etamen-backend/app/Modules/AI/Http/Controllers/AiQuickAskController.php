<?php

namespace App\Modules\AI\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AI\Application\Services\AiAssistantService;
use App\Modules\AI\Application\Services\AiConversationService;
use App\Modules\AI\Http\Requests\AiMessageRequest;
use App\Modules\AI\Http\Resources\AiConversationResource;
use App\Modules\AI\Http\Resources\AiMessageResource;

class AiQuickAskController extends ApiController
{
    public function __construct(
        private readonly AiConversationService $conversations,
        private readonly AiAssistantService $assistant,
    ) {}

    public function __invoke(AiMessageRequest $request)
    {
        $conversation = $this->conversations->create($request->user(), [
            'title' => str($request->validated('content'))->limit(80)->toString(),
            'language' => $request->validated('language', 'ar'),
        ]);
        $message = $this->assistant->send($request->user(), $conversation, $request->validated());

        return $this->success([
            'conversation' => new AiConversationResource($conversation->refresh()),
            'message' => new AiMessageResource($message),
        ], 'AI assistant response created.', 201);
    }
}
