<?php

namespace App\Http\Middleware;

class EnsureUserRole
{
    public function handle($request, \Closure $next, ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403);
        }
        return $next($request);
    }
}