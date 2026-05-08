<?php

namespace App\Modules\Providers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderBookingSetting extends Model
{
    protected $fillable = [
        'provider_id',
        'clinic_visit_enabled',
        'online_video_enabled',
        'home_visit_enabled',
        'branch_visit_enabled',
        'booking_requires_payment',
        'pay_at_branch_enabled',
        'default_slot_duration_minutes',
        'cancellation_policy_ar',
        'cancellation_policy_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'clinic_visit_enabled' => 'boolean',
            'online_video_enabled' => 'boolean',
            'home_visit_enabled' => 'boolean',
            'branch_visit_enabled' => 'boolean',
            'booking_requires_payment' => 'boolean',
            'pay_at_branch_enabled' => 'boolean',
            'default_slot_duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
