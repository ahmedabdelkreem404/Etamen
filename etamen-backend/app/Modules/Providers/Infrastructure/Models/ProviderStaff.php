<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
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
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'role' => ProviderStaffRole::class,
            'is_owner' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function effectivePermissions(): array
    {
        if ($this->is_owner || $this->role === ProviderStaffRole::Owner) {
            return ProviderPermission::ownerValues();
        }

        $custom = collect($this->permissions ?? [])
            ->filter(fn (mixed $permission): bool => is_string($permission))
            ->values()
            ->all();

        if ($custom !== []) {
            return array_values(array_intersect($custom, ProviderPermission::values()));
        }

        if ($this->role === ProviderStaffRole::Admin) {
            return ProviderPermission::adminDefaults();
        }

        return ProviderPermission::staffDefaults();
    }

    public function hasPermission(ProviderPermission|string $permission): bool
    {
        $value = $permission instanceof ProviderPermission ? $permission->value : $permission;

        return in_array($value, $this->effectivePermissions(), true);
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
