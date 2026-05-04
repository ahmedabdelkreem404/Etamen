<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderStaff extends Model
{
    protected $table = 'provider_staff';

    protected $fillable = [
        'provider_id',
        'user_id',
        'role',
        'is_owner',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'role' => ProviderStaffRole::class,
            'is_owner' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
