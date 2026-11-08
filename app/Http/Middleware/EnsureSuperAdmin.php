<?php

namespace App\Http\Middleware;

use App\Support\UserAdministration;
use Closure;
use Illuminate\Http\Request;

/**
 * Restricts platform-administration routes (companies, sections, role creation,
 * company deletion, ...) to super-admins. Roleless users are rejected instead of
 * crashing in User::isSuperAdmin().
 */
class EnsureSuperAdmin
{
	public function handle(Request $request, Closure $next)
	{
		if (! UserAdministration::isSuperAdmin($request->user())) {
			abort(403);
		}

		return $next($request);
	}
}
