<?php

namespace App\Modules\AI\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AI\Application\Services\AiContextBuilderService;
use App\Modules\AI\Application\Services\AiConversationService;
use App\Modules\AI\Http\Requests\AiToggleContextRequest;
use App\Modules\AI\Http\Resources\AiContextPreviewResource;
use App\Modules\AI\Http\Resources\AiConversationResource;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use Illuminate\Http\Request;

class AiContextController extends ApiController
{
    public function __construct(
        private readonly AiContextBuilderService $contextBuilder,
        private readonly AiConversationService $conversations,
    ) {}

    public function preview(Request $request)
    {
        return $this->success(new AiContextPreviewResource($this->contextBuilder->preview($request->user())), 'Safe AI context preview.');
    }

    public function toggle(AiToggleContextRequest $request, AiConversation $conversation)
    {
        $this->authorize('update', $conversation);

        return $this->success(
            new AiConversationResource($this->conversations->toggleContext($request->user(), $conversation, (bool) $request->validated('enabled'))),
            'AI context setting updated.',
        );
    }
}
