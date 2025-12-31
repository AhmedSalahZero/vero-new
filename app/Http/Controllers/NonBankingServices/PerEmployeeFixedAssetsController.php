<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StorePerEmployeeFixedAssetsRequest;
use App\Models\Company;
use App\Models\NonBankingService\Department;
use App\Models\NonBankingService\FixedAsset;
use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;

class PerEmployeeFixedAssetsController extends Controller
{
	public function create(Company $company , Request $request,Study $study){
		return view('non_banking_services.per-employee-fixed-assets.form', $this->getViewVars($company,$study));
	}
	protected function getViewVars(Company $company, Study $study){
		
		$newBranchCountPerDateIndex = $study->getNewBranchCountPerDateIndex();
	
		return [
			'company'=>$company ,
			'type'=>'create',
			'study'=>$study,
			'model'=>$study ,
			'title'=>__('Per Employee Fixed Assets'),
			'fixedAssetType'=>FixedAsset::PER_EMPLOYEE,
			'storeRoute'=>route('store.per.employee.fixed.assets',['company'=>$company->id,'study'=>$study->id]),
			'newBranchCountPerDateIndex'=>$newBranchCountPerDateIndex,
			'departmentFormattedForSelect2'=>Department::where('company_id',$company->id)->get()->formattedForSelect(false,'id','name'),
			
		];
	}
	protected function getRepeaterRelations():array
	{
		return [
			'fixedAssets'
		];
	}
	public function store(Company $company , StorePerEmployeeFixedAssetsRequest $request,Study $study)
	{
		// $fixedAssetType = $request->get('fixed_asset_type') ;
		$fixedAssetType = FixedAsset::PER_EMPLOYEE;
		$oldIdsFromDatabase = $study->fixedAssets->where('type',$fixedAssetType)->pluck('id')->toArray();
		$study->storeRepeaterRelations($request,$this->getRepeaterRelations(),$company,['type'=>$fixedAssetType],$oldIdsFromDatabase);
		$study->recalculateFixedAssets($fixedAssetType);
		$redirectRoute = $study->isExistingCompany() ? route('view.opening.balances.for.non.banking',['company'=>$company->id,'study'=>$study->id])  : route('view.non.banking.forecast.income.statement',['company'=>$company->id , 'study'=>$study->id]);
		
		return response()->json([
			'redirectTo'=>$redirectRoute
		]);
		
	}
}
