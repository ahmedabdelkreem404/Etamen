<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if(! $user, 401);
        abort_if(
            ! $user->hasAnyRole(['doctor', 'pharmacy_admin', 'lab_admin', 'super_admin', 'admin']),
            403,
        );

        return $next($request);
    }
}
