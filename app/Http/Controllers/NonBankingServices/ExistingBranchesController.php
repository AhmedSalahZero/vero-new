<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class ExistingBranchesController extends Controller
{
	use NonBankingService ;
	
	
	public function create(Company $company , Request $request){
		
		return view('non_banking_services.existing-branches.form', $this->getViewVars($company));
	}
	protected function getViewVars(Company $company){
		return [
			'company'=>$company ,
			'model'=>$company ,
			'title'=>__('Existing Branches'),
			'storeRoute'=>route('store.existing.branches',['company'=>$company->id]),
		];
	}
	
	public function store(Company $company , Request $request)
	{
		$company->storeRepeaterRelations($request,['existingBranches'],$company);
		return response()->json([
			'redirectTo'=>route('view.study',['company'=>$company->id])
		]);
	}

}
