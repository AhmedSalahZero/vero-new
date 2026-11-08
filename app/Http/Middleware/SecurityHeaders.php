<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds baseline browser security headers to every response.
 *
 * A Content-Security-Policy is intentionally NOT enforced here: the Blade views
 * rely heavily on inline scripts/handlers, so an enforcing CSP would break the
 * UI. See SECURITY_AUDIT_REPORT.md for the recommended migration path.
 */
class SecurityHeaders
{
	public function handle(Request $request, Closure $next)
	{
		$response = $next($request);

		$headers = [
			'X-Frame-Options' => 'SAMEORIGIN',
			'X-Content-Type-Options' => 'nosniff',
			'Referrer-Policy' => 'strict-origin-when-cross-origin',
			'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
		];
		if ($request->isSecure()) {
			$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
		}

		foreach ($headers as $name => $value) {
			if (! $response->headers->has($name)) {
				$response->headers->set($name, $value);
			}
		}

		return $response;
	}
}
