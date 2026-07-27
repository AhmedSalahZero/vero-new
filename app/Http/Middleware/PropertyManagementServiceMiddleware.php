<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Requires an authenticated user with Property Management system access.
 */
class PropertyManagementServiceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || $user->deleted_at) {
            abort(403);
        }

        if (! $user->hasAccessToSystems([PROPERTY_MANAGEMENT])) {
            abort(403);
        }

        return $next($request);
    }
}
