<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Requires an authenticated user with CashVero system access.
 * Soft-deleted users are rejected even if a stale session resolves.
 */
class CashManagementMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || $user->deleted_at) {
            abort(403);
        }

        if (! $user->hasAccessToSystems([CASH_VERO])) {
            abort(403);
        }

        return $next($request);
    }
}
