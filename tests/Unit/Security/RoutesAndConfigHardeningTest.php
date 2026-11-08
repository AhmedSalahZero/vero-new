<?php

namespace Tests\Unit\Security;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Static hardening checks: removed routes, required middleware, configuration
 * defaults and files that must not be web-reachable. No database needed.
 */
class RoutesAndConfigHardeningTest extends TestCase
{
	public function createApplication()
	{
		putenv('ROUTING_LOCALE=en');
		$_ENV['ROUTING_LOCALE'] = 'en';
		$_SERVER['ROUTING_LOCALE'] = 'en';

		return parent::createApplication();
	}

	protected function tearDown(): void
	{
		putenv('ROUTING_LOCALE');
		unset($_ENV['ROUTING_LOCALE'], $_SERVER['ROUTING_LOCALE']);
		parent::tearDown();
	}

	private function routeByUri(string $uri): ?\Illuminate\Routing\Route
	{
		foreach (Route::getRoutes() as $route) {
			if ($route->uri() === $uri) {
				return $route;
			}
		}

		return null;
	}

	public function test_dangerous_debug_routes_are_gone(): void
	{
		$this->assertNull($this->routeByUri('eee'), 'GET /eee ran migrate + run:sql without authentication');
		foreach (Route::getRoutes() as $route) {
			$this->assertNotSame('second.con', $route->getDomain());
		}
	}

	public function test_public_registration_is_disabled(): void
	{
		$this->assertFalse(Route::has('register'));
		$this->assertNull($this->routeByUri('register'));
	}

	public function test_formerly_open_routes_require_authentication(): void
	{
		foreach ([
			'deleteMultiRowsFromCaching/{company}/{modelName}',
			'deleteAllRowsFromCaching/{company}/{modelType}',
			'get-uploading-percentage/{companyId}/{modelName}',
			'{lang}/remove-company-image/{company}',
			'getStartDateAndEndDateOfIncomeStatementForCompany',
			'removeSessionForRedirect',
		] as $uri) {
			$route = $this->routeByUri($uri);
			$this->assertNotNull($route, $uri);
			$this->assertContains('auth', $route->gatherMiddleware(), $uri.' must require authentication');
		}
	}

	public function test_platform_administration_routes_require_super_admin(): void
	{
		foreach (['companySection.index', 'companySection.update', 'companySection.destroy', 'section.index', 'section.store',
			'remove.company', 'remove.company.image', 'user.permissions.index', 'user.permissions.create', 'user.permissions.store'] as $name) {
			$route = Route::getRoutes()->getByName($name);
			$this->assertNotNull($route, $name);
			$this->assertContains('superAdmin', $route->gatherMiddleware(), $name.' must be super-admin only');
		}
	}

	public function test_web_group_contains_tenant_and_sql_guards_and_headers_are_global(): void
	{
		$kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
		$web = $kernel->getMiddlewareGroups()['web'];
		$this->assertContains(\App\Http\Middleware\canViewCurrentCompany::class, $web);
		$this->assertContains(\App\Http\Middleware\EnsureRouteModelsBelongToCompany::class, $web);
		$this->assertContains(\App\Http\Middleware\GuardAnalysisSqlInput::class, $web);
		$this->assertContains(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, class_parents(\App\Http\Middleware\VerifyCsrfToken::class));
		$this->assertContains(\App\Http\Middleware\VerifyCsrfToken::class, $web);
		$this->assertTrue($kernel->hasMiddleware(\App\Http\Middleware\SecurityHeaders::class));
		$this->assertSame([], (new \ReflectionProperty(\App\Http\Middleware\VerifyCsrfToken::class, 'except'))->getValue(new \App\Http\Middleware\VerifyCsrfToken($this->app, $this->app['encrypter'])));
	}

	public function test_sensitive_user_attributes_are_never_serialized(): void
	{
		$user = new User(['name' => 'N', 'email' => 'n@example.test', 'password' => 'hash', 'odoo_db_password' => 'odoo-secret', 'odoo_username' => 'u']);
		$user->setAttribute('remember_token', 'token-secret');

		$this->assertArrayNotHasKey('password', $user->toArray());
		$this->assertArrayNotHasKey('remember_token', $user->toArray());
		$this->assertArrayNotHasKey('odoo_db_password', $user->toArray());
		$this->assertStringNotContainsString('odoo-secret', $user->toJson());
		$this->assertStringNotContainsString('token-secret', $user->toJson());
	}

	public function test_session_cookie_and_debug_defaults(): void
	{
		$this->assertTrue(config('session.http_only'));
		$this->assertContains(config('session.same_site'), ['lax', 'strict']);
		$appConfig = (string) file_get_contents(config_path('app.php'));
		$this->assertMatchesRegularExpression("/'debug'\s*=>\s*(\(bool\)\s*)?env\('APP_DEBUG',\s*false\)/", $appConfig, 'debug must default to false when APP_DEBUG is not set');
	}

	public function test_production_database_credentials_are_not_hardcoded(): void
	{
		$config = (string) file_get_contents(config_path('database.php'));
		$start = strpos($config, "'mysql_production' => [");
		$this->assertNotFalse($start);
		$block = substr($config, $start, (int) strpos($config, '],', $start) - $start);
		foreach (['host', 'database', 'username', 'password'] as $key) {
			$this->assertMatchesRegularExpression("/'{$key}'\s*=>\s*env\(/", $block, "{$key} must come from env()");
		}
	}

	public function test_public_debug_scripts_are_removed_and_storage_scripts_blocked(): void
	{
		foreach ([
			'assets/vendors/general/jquery.repeater/test-post-parse.php',
			'assets/vendors/general/jquery.repeater/test/echo.php',
			'front/php/process-contact.php',
		] as $file) {
			$this->assertFileDoesNotExist(public_path($file));
		}
		$htaccess = (string) file_get_contents(public_path('.htaccess'));
		$this->assertStringContainsString('RewriteRule ^storage/.*\.(php[0-9]?|phtml|phar|phps)$ - [F,L,NC]', $htaccess);
		$this->assertLessThan(strpos($htaccess, 'RewriteRule ^ index.php'), strpos($htaccess, '^storage/'), 'deny rule must precede the front controller');
	}

	public function test_menu_titles_are_escaped_but_keep_line_breaks(): void
	{
		$this->assertSame('Cash<br>Flow', safeMenuTitle('Cash<br>Flow'));
		$this->assertSame('&lt;img src=x onerror=alert(1)&gt;', safeMenuTitle('<img src=x onerror=alert(1)>'));
		$this->assertSame('A &amp; B', safeMenuTitle('A &amp; B'));
	}
}
