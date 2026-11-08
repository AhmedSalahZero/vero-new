<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteAllSalesGatheringForCompanyJob;
use App\Models\Company;
use App\Models\User;
use App\Support\UserAdministration;
use Illuminate\Http\Request;

class RemoveUsercontroller extends Controller
{

    public function __invoke(Request $request)
    {
        $user_id = $request->get('user_id') ;
     
        $user = User::where('id',$user_id)->firstOrFail();
        // SECURITY: any authenticated user could delete any user.
        $actor = $request->user();
        abort_if($actor->is($user) || ! UserAdministration::canManage($actor, $user), 403);
            $user->delete();
        // dispatch(new DeleteAllSalesGatheringForCompanyJob($companyId));

       return response()->json([
           'status'=>true 
       ]);

    }
}
