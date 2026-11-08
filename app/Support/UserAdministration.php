<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Central authorization rules for user / role / permission management.
 *
 * Mirrors the intent already expressed in the UI (users/form.blade.php,
 * roles_and_permissions/form.blade.php, User::getUsersWithRoles) and makes it
 * enforceable on the server:
 *  - super-admins can manage anyone;
 *  - nobody else can manage a super-admin;
 *  - otherwise the actor must share a company with the target and hold the
 *    matching "view <role>" permission;
 *  - nobody can grant a role or permission they do not hold themselves.
 */
class UserAdministration
{
	/** Role => permission needed to see/manage users with that role. */
	public const VIEW_PERMISSION_FOR_ROLE = [
		User::SUPER_ADMIN => 'view super admin',
		User::COMPANY_ADMIN => 'view company admin',
		User::MANAGER => 'view managers',
		User::USER => 'view users',
	];

	/** Role => permission needed to create/assign users with that role. */
	public const CREATE_PERMISSION_FOR_ROLE = [
		User::COMPANY_ADMIN => 'create company admin',
		User::MANAGER => 'create manager',
		User::USER => 'create user',
	];

	public static function roleName(?User $user): ?string
	{
		return $user?->roles->first()?->name;
	}

	public static function isSuperAdmin(?User $user): bool
	{
		return self::roleName($user) === User::SUPER_ADMIN;
	}

	/**
	 * Raw (non system-scoped) permission names the user holds.
	 *
	 * @return array<int,string>
	 */
	public static function permissionNames(User $user): array
	{
		return $user->getAllPermissions()->pluck('name')->unique()->values()->all();
	}

	public static function holds(User $user, string $permission): bool
	{
		return in_array($permission, self::permissionNames($user), true);
	}

	/**
	 * @return array<int,int>
	 */
	public static function companyIds(User $user): array
	{
		return $user->companies()->pluck('companies.id')->map(fn ($id) => (int) $id)->all();
	}

	public static function canAccessCompany(?User $actor, $companyId): bool
	{
		if (! $actor || ! is_numeric($companyId)) {
			return false;
		}
		if (self::isSuperAdmin($actor)) {
			return true;
		}

		return in_array((int) $companyId, self::companyIds($actor), true);
	}

	public static function shareCompany(User $actor, User $target): bool
	{
		return count(array_intersect(self::companyIds($actor), self::companyIds($target))) > 0;
	}

	public static function canManage(?User $actor, ?User $target): bool
	{
		if (! $actor || ! $target || self::roleName($actor) === null) {
			return false;
		}
		if (self::isSuperAdmin($actor)) {
			return true;
		}
		if (self::isSuperAdmin($target)) {
			return false;
		}
		if ($actor->is($target)) {
			return true;
		}
		if (! self::shareCompany($actor, $target)) {
			return false;
		}
		$targetRole = self::roleName($target);
		if ($targetRole === null) {
			return true;
		}
		$requiredPermission = self::VIEW_PERMISSION_FOR_ROLE[$targetRole] ?? null;

		return $requiredPermission !== null && self::holds($actor, $requiredPermission);
	}

	/**
	 * @return array<int,string>
	 */
	public static function assignableRoles(?User $actor): array
	{
		if (! $actor || self::roleName($actor) === null) {
			return [];
		}
		if (self::isSuperAdmin($actor)) {
			return [User::SUPER_ADMIN, User::COMPANY_ADMIN, User::MANAGER, User::USER];
		}
		$roles = [];
		foreach (self::CREATE_PERMISSION_FOR_ROLE as $role => $permission) {
			if (self::holds($actor, $permission)) {
				$roles[] = $role;
			}
		}

		return $roles;
	}

	public static function canAssignRole(?User $actor, $role, ?User $target = null): bool
	{
		if (! is_string($role) || $role === '') {
			return false;
		}
		if ($target && self::roleName($target) === $role) {
			return true;
		}

		return in_array($role, self::assignableRoles($actor), true);
	}

	/**
	 * Permissions that may actually be synced on $target when $actor requests $requested.
	 * Non super-admins can only grant what they hold, and cannot strip permissions
	 * they do not hold themselves.
	 *
	 * @param  array<int,mixed>  $requested
	 * @return array<int,string>
	 */
	public static function grantablePermissions(User $actor, User $target, array $requested): array
	{
		$requested = array_values(array_filter($requested, 'is_string'));
		$existing = Permission::query()->whereIn('name', $requested)->pluck('name')->all();
		if (self::isSuperAdmin($actor)) {
			return array_values(array_unique($existing));
		}
		$actorPermissions = self::permissionNames($actor);
		$targetCurrent = $target->getDirectPermissions()->pluck('name')->all();
		$granted = array_intersect($existing, $actorPermissions);
		$keptOutOfReach = array_diff($targetCurrent, $actorPermissions);

		return array_values(array_unique(array_merge($granted, $keptOutOfReach)));
	}
}
