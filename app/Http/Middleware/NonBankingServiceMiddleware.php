<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Requires an authenticated user with Non-Banking system access.
 */
class NonBankingServiceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || $user->deleted_at) {
            abort(403);
        }

        if (! $user->hasAccessToSystems([NON_BANKING_SERVICE])) {
            abort(403);
        }

        return $next($request);
    }
}
