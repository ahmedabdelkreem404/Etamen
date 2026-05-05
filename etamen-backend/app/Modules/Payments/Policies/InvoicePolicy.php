<?php

namespace App\Modules\Payments\Policies;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\Invoice;

class InvoicePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return (int) $invoice->payment->user_id === (int) $user->id;
    }
}
