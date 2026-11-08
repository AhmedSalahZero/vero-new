<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Section;
use App\Models\User;
use App\Support\UserAdministration;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsController extends Controller
{
    // public function index($scope,Company $company = null)
    // {
    //     $roles = Role::where('scope',$scope)->get();
    //     return view('super_admin_view.roles_and_permissions.index',compact('scope','roles','company'));
    // }
    // public function create($scope)
    // {
    //     $sections = Section::where('section_side','client')->get();
    //     return view('super_admin_view.roles_and_permissions.form',compact('scope','sections'));
    // }
    // public function store(Request $request ,$scope)
    // {
    //     $role = Role::create(['name' => $request->role,'scope' => $scope]);
    //     $role->syncPermissions(array_keys($request->permissions));
    //     toastr()->success(__('Created Successfully'));
    //     return redirect()->back();

    // }
	public function __construct()
	{
		$this->middleware(['can:update permissions'])->only(['edit','update']);
	}
    public function edit($scope,?Company $company=null)
    {
		$authUser = auth()->user();
		if (UserAdministration::isSuperAdmin($authUser)) {
			$companies = $company ? Company::where('id',$company->id)->get() : Company::all();
		} else {
			// SECURITY: non super-admins only see (and pick users of) their own companies.
			abort_if($company && $company->exists && ! UserAdministration::canAccessCompany($authUser, $company->id), 403);
			$companies = Company::whereIn('id', $company && $company->exists ? [$company->id] : UserAdministration::companyIds($authUser))->get();
		}
        $sections = Section::where('section_side','client')->get();
        return view('super_admin_view.roles_and_permissions.form',compact('scope','sections','companies','company'));
    }
    public function update(Request $request )
    {
		// SECURITY: the target user came from the request with no check, so any holder
		// of "update permissions" (incl. managers) could grant anything to anyone —
		// themselves included. The target must be manageable by the actor and only
		// permissions the actor holds can be granted.
		$user = User::findOrFail($request->get('user_id'));
		abort_unless(UserAdministration::canManage($request->user(), $user), 403);
		$user->syncPermissions(UserAdministration::grantablePermissions($request->user(), $user, array_keys((array) $request->permissions)));
        toastr()->success(__('updated Successfully'));
        return redirect()->back()->withInput();
    }
}
