<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Privilege escalation / broken access control in user, role and permission
 * management (UserController, UsersAndPermissionsController,
 * RolesAndPermissionsController, RemoveUsercontroller, RemoveCompanycontroller).
 */
class UserManagementAuthorizationTest extends SecurityTestCase
{
	private Company $companyA;
	private Company $companyB;
	private User $superAdmin;
	private User $companyAdminA;
	private User $plainUserA;
	private User $userB;

	protected function setUp(): void
	{
		parent::setUp();
		$this->companyA = $this->makeCompany('Users A');
		$this->companyB = $this->makeCompany('Users B');
		$this->superAdmin = $this->makeUser(User::SUPER_ADMIN, [$this->companyA]);
		$this->companyAdminA = $this->makeUser(User::COMPANY_ADMIN, [$this->companyA], null, ['max_users' => 50]);
		$this->plainUserA = $this->makeUser(User::USER, [$this->companyA]);
		$this->userB = $this->makeUser(User::USER, [$this->companyB]);
	}

	private function updatePayload(User $target, array $overrides = []): array
	{
		return array_merge([
			'name' => $target->name,
			'email' => $target->email,
			'role' => $target->getRoleName(),
			'companies' => $target->companies->pluck('id')->all(),
		], $overrides);
	}

	public function test_company_admin_cannot_update_users_of_other_companies_or_super_admins(): void
	{
		$this->actAs($this->companyAdminA)
			->put(route('user.update', ['user' => $this->userB->id]), $this->updatePayload($this->userB, ['name' => 'pwned']))
			->assertForbidden();
		$this->assertNotSame('pwned', $this->userB->fresh()->name);

		$this->actAs($this->companyAdminA)
			->put(route('user.update', ['user' => $this->superAdmin->id]), $this->updatePayload($this->superAdmin, ['email' => 'mine@evil.test']))
			->assertForbidden();
		$this->assertNotSame('mine@evil.test', $this->superAdmin->fresh()->email);
	}

	public function test_company_admin_cannot_promote_anyone_to_super_admin(): void
	{
		$this->actAs($this->companyAdminA)
			->put(route('user.update', ['user' => $this->plainUserA->id]), $this->updatePayload($this->plainUserA, ['role' => User::SUPER_ADMIN]))
			->assertForbidden();
		$this->assertSame(User::USER, $this->plainUserA->fresh()->getRoleName());

		$this->actAs($this->companyAdminA)
			->put(route('user.update', ['user' => $this->companyAdminA->id]), $this->updatePayload($this->companyAdminA, ['role' => User::SUPER_ADMIN]))
			->assertForbidden();
		$this->assertSame(User::COMPANY_ADMIN, $this->companyAdminA->fresh()->getRoleName());
	}

	public function test_update_never_mass_assigns_protected_fields_and_hashes_passwords(): void
	{
		$this->actAs($this->companyAdminA)->put(route('user.update', ['user' => $this->plainUserA->id]), $this->updatePayload($this->plainUserA, [
			'password' => 'BrandNewPass9',
			'password_confirmation' => 'BrandNewPass9',
			'created_by' => $this->superAdmin->id,
			'max_users' => 9999,
			'odoo_db_password' => 'injected',
		]))->assertRedirect();

		$fresh = $this->plainUserA->fresh();
		$this->assertTrue(Hash::check('BrandNewPass9', $fresh->password), 'password must be stored hashed');
		$this->assertNotSame('BrandNewPass9', $fresh->password);
		$this->assertNotEquals($this->superAdmin->id, $fresh->created_by);
		$this->assertNotEquals('9999', (string) $fresh->max_users);
		$this->assertNull($fresh->odoo_db_password);

		// Self-service quota bump is impossible for a company admin.
		$this->actAs($this->companyAdminA)->put(route('user.update', ['user' => $this->companyAdminA->id]), $this->updatePayload($this->companyAdminA, ['max_users' => 100000]));
		$this->assertSame('50', (string) $this->companyAdminA->fresh()->max_users);
	}

	public function test_a_plain_hash_cannot_be_planted_as_password(): void
	{
		$knownHash = Hash::make('attacker-knows-this');
		$this->actAs($this->companyAdminA)->put(route('user.update', ['user' => $this->plainUserA->id]), $this->updatePayload($this->plainUserA, [
			'password' => $knownHash, 'password_confirmation' => $knownHash,
		]));
		$this->assertFalse(Hash::check('attacker-knows-this', $this->plainUserA->fresh()->password));
	}

	public function test_blank_password_on_update_keeps_existing_password(): void
	{
		$this->actAs($this->companyAdminA)->put(route('user.update', ['user' => $this->plainUserA->id]), $this->updatePayload($this->plainUserA, ['password' => '']))->assertRedirect();
		$this->assertTrue(Hash::check('Secret-for-tests-1', $this->plainUserA->fresh()->password));
	}

	public function test_store_validates_role_and_password(): void
	{
		$before = User::count();
		$this->actAs($this->companyAdminA)->post(route('user.store'), [
			'name' => 'New', 'email' => 'new-'.uniqid().'@example.test', 'password' => 'Password123', 'password_confirmation' => 'Password123',
			'role' => User::SUPER_ADMIN, 'companies' => [$this->companyA->id],
		])->assertSessionHasErrors('role');
		$this->actAs($this->companyAdminA)->post(route('user.store'), [
			'name' => 'New', 'email' => 'new-'.uniqid().'@example.test', 'password' => 'short', 'password_confirmation' => 'short',
			'role' => User::USER, 'companies' => [$this->companyA->id],
		])->assertSessionHasErrors('password');
		$this->assertSame($before, User::count());

		$email = 'ok-'.uniqid().'@example.test';
		$this->actAs($this->companyAdminA)->post(route('user.store'), [
			'name' => 'Created', 'email' => $email, 'password' => 'Password123', 'password_confirmation' => 'Password123',
			'role' => User::USER, 'companies' => [$this->companyA->id], 'created_by' => $this->superAdmin->id, 'max_users' => 500,
		])->assertRedirect();
		$created = User::where('email', $email)->firstOrFail();
		$this->assertSame($this->companyAdminA->id, (int) $created->created_by);
		$this->assertNull($created->max_users);
		$this->assertTrue(Hash::check('Password123', $created->password));
	}

	public function test_plain_user_cannot_create_users(): void
	{
		$this->actAs($this->plainUserA)->get(route('user.create'))->assertForbidden();
		$before = User::count();
		$this->actAs($this->plainUserA)->post(route('user.store'), [
			'name' => 'X', 'email' => 'x-'.uniqid().'@example.test', 'password' => 'Password123', 'password_confirmation' => 'Password123', 'role' => User::USER,
		])->assertForbidden();
		$this->assertSame($before, User::count());
	}

	public function test_plain_user_cannot_escalate_own_permissions(): void
	{
		$before = $this->plainUserA->getDirectPermissions()->pluck('name')->sort()->values()->all();

		$this->actAs($this->plainUserA)->post(route('user.permissions.update', ['user' => $this->plainUserA->id]), [
			'permissions' => ['view super admin' => 'on', 'create company admin' => 'on', 'update permissions' => 'on'],
		])->assertForbidden();

		$after = $this->plainUserA->fresh()->getDirectPermissions()->pluck('name')->sort()->values()->all();
		$this->assertSame($before, $after);
		$this->assertNotContains('view super admin', $after);
	}

	public function test_permission_grants_are_limited_to_what_the_actor_holds(): void
	{
		$this->actAs($this->companyAdminA)->post(route('user.permissions.update', ['user' => $this->plainUserA->id]), [
			'permissions' => ['view super admin' => 'on', 'view home' => 'on'],
		])->assertRedirect();
		$granted = $this->plainUserA->fresh()->getDirectPermissions()->pluck('name')->all();
		$this->assertContains('view home', $granted);
		$this->assertNotContains('view super admin', $granted, 'a company admin cannot grant a permission they do not hold');
	}

	public function test_roles_permissions_update_rejects_unmanageable_targets(): void
	{
		foreach ([$this->userB, $this->superAdmin] as $target) {
			$before = $target->getDirectPermissions()->count();
			$this->actAs($this->companyAdminA)->post(route('roles.permissions.update'), [
				'user_id' => $target->id, 'permissions' => [],
			])->assertForbidden();
			$this->assertSame($before, $target->fresh()->getDirectPermissions()->count());
		}
	}

	public function test_users_lookup_requires_membership_and_leaks_nothing_sensitive(): void
	{
		$this->plainUserA->forceFill(['odoo_db_password' => 'odoo-secret-value', 'odoo_username' => 'odoo-user'])->save();
		$url = route('update.users.based.on.company.and.role');

		$this->actAs($this->companyAdminA)->getJson($url.'?companyId='.$this->companyB->id.'&roleName=user')->assertForbidden();
		$this->actAs($this->companyAdminA)->getJson($url.'?roleName=user')->assertForbidden();

		$response = $this->actAs($this->companyAdminA)->getJson($url.'?companyId='.$this->companyA->id.'&roleName=user')->assertOk();
		foreach ($response->json('users') as $row) {
			$this->assertSame(['id', 'name'], array_keys($row));
		}
		$this->assertStringNotContainsString('odoo-secret-value', $response->getContent());
		$this->assertStringNotContainsString($this->plainUserA->email, $response->getContent());
	}

	public function test_render_permissions_for_foreign_user_is_forbidden(): void
	{
		$this->actAs($this->companyAdminA)->getJson(route('render.permissions.html.for.user').'?userId='.$this->userB->id)->assertForbidden();
	}

	public function test_user_list_without_company_does_not_leak_other_tenants(): void
	{
		$response = $this->actAs($this->companyAdminA)->get(route('user.index'))->assertOk();
		// The list renders user names (unique per test run).
		$response->assertSee($this->plainUserA->name);
		$response->assertDontSee($this->userB->name);
		$response->assertDontSee($this->superAdmin->name);
	}

	public function test_remove_user_requires_management_rights(): void
	{
		$this->actAs($this->companyAdminA)->postJson(route('remove.user'), ['user_id' => $this->userB->id])->assertForbidden();
		$this->actAs($this->companyAdminA)->postJson(route('remove.user'), ['user_id' => $this->superAdmin->id])->assertForbidden();
		$this->actAs($this->plainUserA)->postJson(route('remove.user'), ['user_id' => $this->companyAdminA->id])->assertForbidden();
		$this->assertNotNull($this->userB->fresh());
		$this->assertNotNull($this->superAdmin->fresh());
		$this->assertNotNull($this->companyAdminA->fresh());

		$this->actAs($this->companyAdminA)->postJson(route('remove.user'), ['user_id' => $this->plainUserA->id])->assertOk();
		$this->assertNull(User::find($this->plainUserA->id));
	}

	public function test_platform_administration_is_super_admin_only(): void
	{
		$this->actAs($this->companyAdminA)->postJson(route('remove.company'), ['company_id' => $this->companyB->id])->assertForbidden();
		$this->assertNotNull(Company::find($this->companyB->id));

		$this->actAs($this->companyAdminA)->get(route('companySection.index'))->assertForbidden();
		$this->actAs($this->companyAdminA)->get(route('companySection.edit', ['companySection' => $this->companyA->id]))->assertForbidden();
		$this->actAs($this->companyAdminA)->put(route('companySection.update', ['companySection' => $this->companyB->id]), ['name' => ['en' => 'pwned']])->assertForbidden();
		$this->actAs($this->companyAdminA)->get(route('section.index'))->assertForbidden();
		$this->actAs($this->companyAdminA)->post(route('user.permissions.store', ['user' => $this->plainUserA->id]), ['role' => 'evil-role', 'permissions' => ['view super admin' => 'on']])->assertForbidden();
		$this->assertDatabaseMissing('roles', ['name' => 'evil-role']);
	}

	public function test_roleless_users_are_denied_not_crashed(): void
	{
		$roleless = User::create(['name' => 'No Role', 'email' => 'norole-'.uniqid().'@example.test', 'password' => bcrypt('Secret-for-tests-1')]);
		$roleless->companies()->attach($this->companyA->id);

		$this->actAs($roleless)->post(route('user.permissions.update', ['user' => $roleless->id]), ['permissions' => ['view super admin' => 'on']])->assertForbidden();
		$this->actAs($roleless)->postJson(route('remove.company'), ['company_id' => $this->companyB->id])->assertForbidden();
		$this->assertFalse($roleless->fresh()->hasDirectPermission('view super admin'));
	}
}
