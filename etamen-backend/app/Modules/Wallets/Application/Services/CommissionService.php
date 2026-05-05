<?php

namespace App\Modules\Wallets\Application\Services;

use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Wallets\Infrastructure\Models\CommissionRule;

class CommissionService
{
    public function calculate(ProviderType $providerType, ServiceType $serviceType, float $grossAmount): array
    {
        $rule = CommissionRule::query()
            ->where('provider_type', $providerType)
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->first();

        if (! $rule) {
            return [
                'gross_amount' => round($grossAmount, 2),
                'commission_amount' => 0.0,
                'net_amount' => round($grossAmount, 2),
                'rule_id' => null,
                'missing_rule' => true,
            ];
        }

        $percentageAmount = $grossAmount * ((float) $rule->percentage / 100);
        $commission = round($percentageAmount + (float) ($rule->fixed_amount ?? 0), 2);
        $commission = min($commission, round($grossAmount, 2));

        return [
            'gross_amount' => round($grossAmount, 2),
            'commission_amount' => $commission,
            'net_amount' => max(round($grossAmount - $commission, 2), 0),
            'rule_id' => $rule->id,
            'missing_rule' => false,
        ];
    }
}
