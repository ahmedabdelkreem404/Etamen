<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(! $request->user()?->isPlatformAdmin(), 403);

        return $next($request);
    }
}
