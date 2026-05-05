<?php

namespace App\Modules\Wallets\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Wallets\Http\Requests\CommissionRuleRequest;
use App\Modules\Wallets\Http\Resources\CommissionRuleResource;
use App\Modules\Wallets\Infrastructure\Models\CommissionRule;

class AdminCommissionRuleController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index()
    {
        return $this->success(CommissionRuleResource::collection(CommissionRule::query()->orderByDesc('starts_at')->get()), 'Commission rules.');
    }

    public function store(CommissionRuleRequest $request)
    {
        $rule = CommissionRule::query()->create($request->validated());
        $this->auditLogService->log('commission_rule.created', $rule, $request->user());

        return $this->success(new CommissionRuleResource($rule), 'Commission rule created.', 201);
    }

    public function update(CommissionRuleRequest $request, CommissionRule $rule)
    {
        $before = $rule->getAttributes();
        $rule->update($request->validated());
        $this->auditLogService->log('commission_rule.updated', $rule, $request->user(), before: $before, after: $rule->getAttributes());

        return $this->success(new CommissionRuleResource($rule), 'Commission rule updated.');
    }
}
