<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Tenant isolation: the authenticated user must be a member of the company the
 * request targets.
 *
 * SECURITY: this used to look only at URL segment(2). When a request omits the
 * locale prefix (e.g. POST /{company}/...), mcamara/laravel-localization also
 * registers the routes WITHOUT the prefix, so the company id moves to segment(1)
 * and segment(2) is non-numeric — the check silently passed and any user could
 * write to any company. The company is now taken from the matched route's
 * {company} parameter whenever the route is company-scoped, falling back to the
 * legacy segment(2) rule for the remaining routes.
 */
class canViewCurrentCompany
{
	private const COMPANY_SCOPED_URI = '#^(?:[^/{}]+/)?\{company\}(?:/|$)#';

	public function handle(Request $request, Closure $next)
	{
		$user = $request->user();
		if (! $user) {
			// Authentication (where required) is enforced by the `auth` middleware.
			return $next($request);
		}

		$companyId = self::targetCompanyId($request);
		if ($companyId === null || $user->companies->contains('id', $companyId)) {
			return $next($request);
		}

		abort(403);
	}

	public static function targetCompanyId(Request $request): ?int
	{
		$route = $request->route();
		if ($route instanceof Route && preg_match(self::COMPANY_SCOPED_URI, $route->uri())) {
			$company = $route->parameter('company');
			if ($company instanceof Company) {
				return (int) $company->getKey();
			}
			if (is_numeric($company)) {
				return (int) $company;
			}
		}

		$segment = $request->segment(2);

		return is_numeric($segment) ? (int) $segment : null;
	}
}
