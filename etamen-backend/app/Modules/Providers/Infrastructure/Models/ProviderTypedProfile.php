<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

abstract class ProviderTypedProfile extends Model
{
    abstract protected static function requiredProviderType(): ProviderType;

    protected static function booted(): void
    {
        static::saving(function (ProviderTypedProfile $profile): void {
            $provider = Provider::query()->find($profile->provider_id);

            if (! $provider || $provider->type !== static::requiredProviderType()) {
                throw ValidationException::withMessages([
                    'provider_id' => ['Provider type does not match this profile table.'],
                ]);
            }
        });
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
