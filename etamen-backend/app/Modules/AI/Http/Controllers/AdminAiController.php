<?php

namespace App\Modules\AI\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AI\Application\Services\AiProviderConfigService;
use App\Modules\AI\Http\Requests\AiProviderConfigRequest;
use App\Modules\AI\Http\Resources\AiConversationResource;
use App\Modules\AI\Http\Resources\AiMessageResource;
use App\Modules\AI\Http\Resources\AiProviderConfigResource;
use App\Modules\AI\Http\Resources\AiSafetyEventResource;
use App\Modules\AI\Http\Resources\AiUsageLogResource;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use App\Modules\AI\Infrastructure\Models\AiProviderConfig;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use App\Modules\AI\Infrastructure\Models\AiUsageLog;
use Illuminate\Http\Request;

class AdminAiController extends ApiController
{
    public function __construct(private readonly AiProviderConfigService $providerConfigs) {}

    public function conversations(Request $request)
    {
        $query = AiConversation::query()->withCount('messages')->latest('id');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('provider')) {
            $query->where('provider', (string) $request->string('provider'));
        }

        return $this->success(AiConversationResource::collection($query->paginate($this->perPage($request, 50))), 'AI conversations.');
    }

    public function conversation(AiConversation $conversation)
    {
        return $this->success(new AiConversationResource($conversation->loadCount('messages')), 'AI conversation details.');
    }

    public function messages(Request $request)
    {
        $query = AiMessage::query()->latest('id');

        if ($request->filled('safety_classification')) {
            $query->where('safety_classification', (string) $request->string('safety_classification'));
        }

        return $this->success(AiMessageResource::collection($query->paginate($this->perPage($request, 50))), 'AI messages.');
    }

    public function safetyEvents(Request $request)
    {
        $query = AiSafetyEvent::query()->latest('id');

        if ($request->filled('severity')) {
            $query->where('severity', (string) $request->string('severity'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', (string) $request->string('event_type'));
        }

        return $this->success(AiSafetyEventResource::collection($query->paginate($this->perPage($request, 50))), 'AI safety events.');
    }

    public function usageLogs(Request $request)
    {
        return $this->success(AiUsageLogResource::collection(AiUsageLog::query()->latest('id')->paginate($this->perPage($request, 50))), 'AI usage logs.');
    }

    public function providerConfigs()
    {
        return $this->success(AiProviderConfigResource::collection($this->providerConfigs->ensureDefaults()), 'AI provider configs.');
    }

    public function updateProviderConfig(AiProviderConfigRequest $request, AiProviderConfig $config)
    {
        return $this->success(
            new AiProviderConfigResource($this->providerConfigs->update($request->user(), $config, $request->validated())),
            'AI provider config updated.',
        );
    }
}
