<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Base class for the security regression suite.
 *
 * Runs against the ISOLATED schema-only database `property_managements_test`
 * (build it with `scripts/setup-test-db.sh`), never against development or
 * production data. Every test is wrapped in a transaction and rolled back.
 */
abstract class SecurityTestCase extends TestCase
{
	use DatabaseTransactions;

	public const TEST_DATABASE = 'property_managements_test';

	/** Register routes with the "en" locale prefix, as in production URLs. */
	protected bool $withLocalePrefix = true;

	public function createApplication()
	{
		if ($this->withLocalePrefix) {
			putenv('ROUTING_LOCALE=en');
			$_ENV['ROUTING_LOCALE'] = 'en';
			$_SERVER['ROUTING_LOCALE'] = 'en';
		} else {
			putenv('ROUTING_LOCALE');
			unset($_ENV['ROUTING_LOCALE'], $_SERVER['ROUTING_LOCALE']);
		}

		return parent::createApplication();
	}

	protected function setUpTraits()
	{
		config(['database.connections.mysql.database' => self::TEST_DATABASE]);
		DB::purge('mysql');

		return parent::setUpTraits();
	}

	protected function setUp(): void
	{
		parent::setUp();

		$database = DB::connection('mysql')->getDatabaseName();
		if (! str_ends_with((string) $database, '_test')) {
			$this->fail("Security tests must run against a *_test database, got [{$database}].");
		}

		try {
			$roles = DB::table('roles')->whereIn('name', [User::SUPER_ADMIN, User::COMPANY_ADMIN, User::MANAGER, User::USER])->count();
			$permissions = DB::table('permissions')->count();
		} catch (\Throwable $e) {
			$this->markTestSkipped('Test database not ready ('.$e->getMessage().'). Run: MYSQL_PWD=... bash scripts/setup-test-db.sh');
		}
		if ($roles < 4 || $permissions === 0) {
			$this->markTestSkipped('Roles/permissions missing in '.self::TEST_DATABASE.'. Run: MYSQL_PWD=... bash scripts/setup-test-db.sh');
		}

		app(PermissionRegistrar::class)->forgetCachedPermissions();
		$this->withoutMiddleware([
			LocaleSessionRedirect::class,
			LaravelLocalizationRedirectFilter::class,
			LaravelLocalizationViewPath::class,
		]);
	}

	protected function tearDown(): void
	{
		putenv('ROUTING_LOCALE');
		unset($_ENV['ROUTING_LOCALE'], $_SERVER['ROUTING_LOCALE']);

		parent::tearDown();
	}

	/** @param string[] $systems */
	protected function makeCompany(string $name = 'Security Co', array $systems = [CASH_VERO]): Company
	{
		$company = Company::create(['name' => ['en' => $name.' '.uniqid(), 'ar' => $name]]);
		foreach ($systems as $system) {
			CompanySystem::create(['company_id' => $company->id, 'system_name' => $system]);
		}

		return $company->fresh('systems');
	}

	/**
	 * @param  Company[]  $companies
	 * @param  string[]|null  $permissions  null = the role's default catalogue permissions
	 */
	protected function makeUser(string $role, array $companies, ?array $permissions = null, array $attributes = []): User
	{
		/** @var User $user */
		$user = User::create(array_merge([
			'name' => 'Security '.$role.' '.uniqid(),
			'email' => 'security-'.uniqid().'@example.test',
			'password' => bcrypt('Secret-for-tests-1'),
		], $attributes));
		$user->assignRole($role);
		foreach ($companies as $company) {
			$user->companies()->attach($company->id);
		}
		$user->givePermissionTo($permissions ?? $this->defaultPermissionsFor($role));
		app(PermissionRegistrar::class)->forgetCachedPermissions();

		return $user->fresh(['roles', 'permissions', 'companies']);
	}

	/** @return string[] */
	protected function defaultPermissionsFor(string $role): array
	{
		$names = [];
		foreach (\App\Helpers\HAuth::getPermissions() as $permission) {
			if (in_array($role, $permission['default-roles'] ?? [], true)) {
				$names[] = $permission['name'];
			}
		}
		$existing = DB::table('permissions')->whereIn('name', $names)->pluck('name')->all();

		return array_values(array_unique($existing));
	}

	protected function actAs(User $user): static
	{
		app()->forgetInstance(Company::class);
		$this->actingAs($user);

		return $this;
	}
}
