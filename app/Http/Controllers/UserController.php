<?php

namespace App\Http\Controllers;

use App\Helpers\HArr;
use App\Helpers\HAuth;
use App\Models\Company;
use App\Models\User;
use App\Support\UserAdministration;
use App\Traits\ImageSave;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
	use ImageSave;

	/** Raster images only — SVG/HTML are excluded (stored on the public disk). */
	public const IMAGE_RULES = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'];
	public function __construct()
	{
		$this->middleware(['can:view users'])->only(['index']);
	}
	public function freeSubscription(Request $request)
	{
		if ($request->isMethod('POST')) {
			$this->validate($request, [

				'name' => ['required', 'string', 'max:255'],
				'company_name' => 'required',
				'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
				'avatar' =>  'required',
				'company_avatar' =>  'required',
				'password' => ['required', 'string', 'min:8', 'confirmed'],
			]);

			$request['password'] = Hash::make($request->password);

			$dt = Carbon::parse(date('Y-m-d'));
			$expiration_date = $dt->addDays(15)->format('Y-m-d');
			$user = User::create($request->except('avatar'));
			$user->subscription = 'free_trial';
			$user->expiration_date = $expiration_date;
			$user->save();

			ImageSave::saveIfExist('avatar', $user);

			$companySection = Company::create(['name' => $request->company_name]);
			ImageSave::saveIfExist('company_avatar', $companySection);

			$user->companies()->attach($companySection->id);
			
			
			$user->assignRole('user');

		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->clearClassPermissions();
		$permissions = HAuth::getPermissions();
		foreach ($permissions as $permissionArr) {
			// if($permission !='view sales forecast quantity base'){
				$permission =Permission::findByName($permissionArr['name']);;
				$user->assignNewPermission($permissionArr,$permission);
			// }
		}
			Auth::login($user, $remember = true);

			return redirect()->route('home');
		} else {
			return view('free_subscription.form');
		}
	}

	
	public function index(?Company $company = null)
	{

		$users = collect([]);
		$authUser = Auth()->user() ;
		// SECURITY: a user list for a company the actor does not belong to was readable.
		abort_if($company && $company->exists && ! UserAdministration::canAccessCompany($authUser, $company->id), 403);
		/**
		 * @var \App\Models\User $authUser;
		 */
		$users = User::getUsersWithRoles($company);

		return view('super_admin_view.users.index', compact('users','company'));
	}


	public function create(?Company $company = null)
	{
		$authUser = Auth()->user();
		abort_if(UserAdministration::assignableRoles($authUser) === [], 403);
		$companies = $this->companiesForForm($authUser, $company);
		$canEditCompanies = $this->authUserCanEditCompanyAssignment($authUser);

		return view('super_admin_view.users.form', compact('companies', 'company', 'canEditCompanies'));
	}

	
	public function store(Request $request)
	{
		$user = Auth()->user();
		/**
		 * @var User $user
		 */
		// SECURITY: role, password and quota fields were taken from the request
		// unchecked (any role incl. super-admin, no password policy).
		abort_if(UserAdministration::assignableRoles($user) === [], 403);
		$isSuperAdmin = UserAdministration::isSuperAdmin($user);
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
			'password' => ['required', 'string', 'min:8', 'confirmed'],
			'role' => ['required', 'string', \Illuminate\Validation\Rule::in(UserAdministration::assignableRoles($user))],
			'max_users' => ['nullable', 'integer', 'min:0'],
			'avatar' => self::IMAGE_RULES,
			'image' => self::IMAGE_RULES,
		]);
		if (!$user->canStoreMoreUser()) {
			return redirect()->back()->with('fail', __('You Exceed Your Max Users [ ' . $user->max_users . ' ]'));
		}
		$companyIds = $this->resolveCompanyIdsForWrite($user, $request->input('companies'), null);
		abort_unless(count($companyIds) > 0, 422, __('Select at least one company.'));
		$attributes = [
			'name' => $validated['name'],
			'email' => $validated['email'],
			'password' => Hash::make($validated['password']),
			'created_by' => Auth()->user()->id,
		];
		if ($isSuperAdmin && $request->filled('max_users')) {
			$attributes['max_users'] = $validated['max_users'];
		}

		$user = User::create($attributes);
		$user->companies()->attach($companyIds);
		$user->assignRole($validated['role']);
		/**
		 * @var User $user
		 */
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->clearClassPermissions();
		$permissions = HAuth::getPermissions($user->getSystemsNames());
		foreach ($permissions as $permissionArr) {
			$permission = Permission::findByName($permissionArr['name']);
			$user->assignNewPermission($permissionArr,$permission);
		}

		ImageSave::saveIfExist('image', $user);

		return redirect()->back();
	}

	
	public function show($id)
	{

	}

	
	public function edit(User $user)
	{
		$authUser = Auth()->user();
		abort_unless(UserAdministration::canManage($authUser, $user), 403);
		$companies = $this->companiesForForm($authUser, null);
		$canEditCompanies = $this->authUserCanEditCompanyAssignment($authUser);

		return view('super_admin_view.users.form', compact('companies', 'user', 'canEditCompanies'));
	}

	
	public function update(Request $request, User $user)
	{
		$authUser = Auth()->user();
		// SECURITY: previously any authenticated CashVero user could update any
		// user, the whole request was mass-assigned (password stored UNHASHED,
		// created_by / max_users / odoo_* overwritable) and any role — including
		// super-admin — could be assigned.
		abort_unless(UserAdministration::canManage($authUser, $user), 403);
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
			'password' => ['nullable', 'string', 'min:8', 'confirmed'],
			'role' => ['nullable', 'string'],
			'max_users' => ['nullable', 'integer', 'min:0'],
			'avatar' => self::IMAGE_RULES,
		]);
		$requestedRole = $validated['role'] ?? null;
		if ($requestedRole !== null) {
			abort_unless(UserAdministration::canAssignRole($authUser, $requestedRole, $user), 403);
		}
		$companyIds = $this->resolveCompanyIdsForWrite($authUser, $request->input('companies'), $user);
		abort_unless(count($companyIds) > 0, 422, __('Select at least one company.'));

		$attributes = ['name' => $validated['name'], 'email' => $validated['email']];
		if (! empty($validated['password'])) {
			$attributes['password'] = Hash::make($validated['password']);
		}
		if (UserAdministration::isSuperAdmin($authUser) && $request->filled('max_users')) {
			$attributes['max_users'] = $validated['max_users'];
		}
		$user->forceFill($attributes)->save();
		$user->companies()->sync($companyIds);
		if ($requestedRole !== null && UserAdministration::roleName($user) !== $requestedRole) {
			$user->syncRoles([$requestedRole]);
		}
		ImageSave::saveIfExist('avatar', $user);

		return redirect()->back();
	}

	/**
	 * Company IDs the acting user may assign. null = unrestricted (Super Admin).
	 *
	 * @return list<int>|null
	 */
	protected function authUserAssignableCompanyIds(User $authUser): ?array
	{
		if ($authUser->isSuperAdmin()) {
			return null;
		}

		return $authUser->companies->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
	}

	/**
	 * Owner policy (2026-07-26): only Super Admin freely picks any company.
	 * Non–Super Admin is limited to their own companies; with a single company
	 * they cannot change the target user's company assignment at all.
	 */
	protected function authUserCanEditCompanyAssignment(User $authUser): bool
	{
		if ($authUser->isSuperAdmin()) {
			return true;
		}

		return $authUser->companies->count() > 1;
	}

	protected function companiesForForm(User $authUser, ?Company $company)
	{
		$assignableIds = $this->authUserAssignableCompanyIds($authUser);

		if ($company) {
			abort_unless(
				$assignableIds === null || in_array((int) $company->id, $assignableIds, true),
				403
			);

			return Company::where('id', $company->id)->get();
		}

		if ($assignableIds === null) {
			return Company::all();
		}

		return Company::whereIn('id', $assignableIds)->get();
	}

	/**
	 * @param  array<int|string>|null  $submitted
	 * @return list<int>
	 */
	protected function resolveCompanyIdsForWrite(User $authUser, ?array $submitted, ?User $existingUser): array
	{
		$assignable = $this->authUserAssignableCompanyIds($authUser);
		$submittedIds = array_values(array_unique(array_map('intval', (array) $submitted)));

		if ($assignable === null) {
			return $submittedIds;
		}

		if (! $this->authUserCanEditCompanyAssignment($authUser)) {
			if ($existingUser) {
				return $existingUser->companies->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
			}

			return $assignable;
		}

		$forbidden = array_diff($submittedIds, $assignable);
		abort_unless($forbidden === [], 403, __('You can only assign companies you belong to.'));

		return array_values(array_intersect($submittedIds, $assignable));
	}

	
	public function destroy($id)
	{

	}
	public function getUsersBasedOnCompanyAndRole(Request $request){
		$roleName = $request->input('roleName');
		$companyId = $request->input('companyId');
		// SECURITY: companyId came from the request without a membership check and a
		// missing company returned ALL users with every attribute (incl. Odoo password).
		abort_unless(UserAdministration::canAccessCompany($request->user(), $companyId), 403);
		$company = Company::findOrFail($companyId);
		$users = User::getUsersWithRoles($company,$roleName)
			->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])
			->values();
		return response()->json([
			'users'=>$users
		]);
	}
	public function renderPermissionForUser(Request $request)
	{
		$user = User::findOrFail($request->get('userId'));
		abort_unless(UserAdministration::canManage($request->user(), $user), 403);
		$permissionViews = view('super_admin_view.roles_and_permissions.permissions-radio',['user'=>$user])->render();
		return response()->json([
			'view'=>$permissionViews
		]);
	}
}
