<?php

namespace App\Http\Controllers;

use App\Helpers\HArr;
use App\Helpers\HAuth;
use App\Models\Company;
use App\Models\User;
use App\Traits\ImageSave;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
	use ImageSave;
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
		/**
		 * @var \App\Models\User $authUser;
		 */
		$users = User::getUsersWithRoles($company);

		return view('super_admin_view.users.index', compact('users','company'));
	}


	public function create(?Company $company = null)
	{
		$authUser = Auth()->user();
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
		$request->validate([
			'email'=>'unique:users,email'
		]);
		if (!$user->canStoreMoreUser()) {
			return redirect()->back()->with('fail', __('You Exceed Your Max Users [ ' . $user->max_users . ' ]'));
		}
		$companyIds = $this->resolveCompanyIdsForWrite($user, $request->input('companies'), null);
		abort_unless(count($companyIds) > 0, 422, __('Select at least one company.'));
		$request['password'] = Hash::make($request->password);
		$request['subscription'] = 'subscripted';

		$user = User::create(
			array_merge(
				$request->except('avatar', 'companies'),
				['created_by'=>Auth()->user()->id]
			),
		);
		$user->companies()->attach($companyIds);
		$user->assignRole($request->role);
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
		$companies = $this->companiesForForm($authUser, null);
		$canEditCompanies = $this->authUserCanEditCompanyAssignment($authUser);

		return view('super_admin_view.users.form', compact('companies', 'user', 'canEditCompanies'));
	}

	
	public function update(Request $request, User $user)
	{
		$authUser = Auth()->user();
		$companyIds = $this->resolveCompanyIdsForWrite($authUser, $request->input('companies'), $user);
		abort_unless(count($companyIds) > 0, 422, __('Select at least one company.'));

		$user->update($request->except('avatar', 'companies'));
		$user->companies()->sync($companyIds);
		@count($user->roles) == 0 ?: $user->removeRole($user->roles[0]->name);

		$user->assignRole($request->role);
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
		$company = Company::find($companyId);
		$users = User::getUsersWithRoles($company,$roleName);
		return response()->json([
			'users'=>$users
		]);
	}
	public function renderPermissionForUser(Request $request)
	{
		$user = User::find($request->get('userId'));
		$permissionViews = view('super_admin_view.roles_and_permissions.permissions-radio',['user'=>$user])->render();
		return response()->json([
			'view'=>$permissionViews
		]);
	}
}
