<?php

namespace App\Http\Controllers\NonBankingServices;


use App\Helpers\HHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreNewBranchFixedAssetsRequest;
use App\Models\Company;
use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\FixedAsset;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class NewBranchFixedAssetsController extends Controller
{
	use NonBankingService ;
	public function create(Company $company , Request $request,Study $study){
		return view('non_banking_services.new-branch-fixed-assets.form', $this->getViewVars($company,$study));
	}
	protected function getViewVars(Company $company, Study $study){
		$studyMonthsForViews = $study->getStudyDurationPerYearFromIndexesForView();
		$yearWithItsIndexes = $study->getStudyDurationPerYearFromIndexes();
		$newBranchCountPerDateIndex = $study->getNewBranchCountPerDateIndex();
			$fundingStructureCounts = $study->getFixedAssetsWithCountsDates(FixedAsset::NEW_BRANCH);
		return [
			'fundingStructureCounts'=>$fundingStructureCounts,
			'company'=>$company ,
			'type'=>'create',
			'study'=>$study,
			'model'=>$study ,
			'expenseType'=>HHelpers::getClassNameWithoutNameSpace((new Expense())),
			'title'=>__('New Branches Fixed Assets'),
			'monthsWithItsYear' => $yearWithItsIndexes,
			'studyMonthsForViews'=>$studyMonthsForViews,
			'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
			'fixedAssetType'=>FixedAsset::NEW_BRANCH,
			'storeRoute'=>route('store.new.branch.fixed.assets',['company'=>$company->id,'study'=>$study->id]),
			'newBranchCountPerDateIndex'=>$newBranchCountPerDateIndex
		];
	}
	protected function getRepeaterRelations():array
	{
		return [
			'fixedAssets'
		];
	}
	public function store(Company $company , StoreNewBranchFixedAssetsRequest $request,Study $study)
	{
		$fixedAssetType = $request->get('fixed_asset_type') ;
		$oldIdsFromDatabase = $study->fixedAssets->where('type',$fixedAssetType)->pluck('id')->toArray();
		$study->storeRepeaterRelations($request,$this->getRepeaterRelations(),$company,['type'=>$fixedAssetType],$oldIdsFromDatabase);
		$study->recalculateFixedAssets($fixedAssetType);
		$redirectRoute = route('create.per.employee.fixed.assets',['company'=>$company->id,'study'=>$study->id]);
		return response()->json([
			'redirectTo'=>$redirectRoute
		]);
		
	}
	 public function storeFunding(Company $company, Request $request, Study $study)
    {
        $fixedAssetType = $request->get('fixed_asset_type') ;

        $study->storeRelationsWithNoRepeater($request, $company);

		$study->recalculateFixedAssets($fixedAssetType);
        
        return response()->json([
            'redirectTo'=>route('view.opening.balances.for.non.banking', ['company'=>$company->id,'study'=>$study->id])
        ]);
        
    }
}
