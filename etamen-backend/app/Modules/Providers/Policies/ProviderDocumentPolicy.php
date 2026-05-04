<?php

namespace App\Modules\Providers\Policies;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;

class ProviderDocumentPolicy
{
    public function view(User $user, ProviderDocument $providerDocument): bool
    {
        return $user->ownsProvider($providerDocument->provider);
    }
}
