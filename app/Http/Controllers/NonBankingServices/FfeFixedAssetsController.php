<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFixedAssetsRequest;
use App\Models\Company;
use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\FixedAsset;
use App\Models\NonBankingService\Study;
use App\Traits\HasFixedAssetFunding;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class FfeFixedAssetsController extends Controller
{
    use NonBankingService ;
    public function create(Company $company, Request $request, Study $study)
    {
        return view('non_banking_services.ffe-fixed-assets.form', $this->getViewVars($company, $study));
    }
    public function createFundingStructure(Company $company, Request $request, Study $study)
    {
        return view('non_banking_services.ffe-fixed-assets.funding-form', $this->getViewVars($company, $study));
    }
    protected function getViewVars(Company $company, Study $study)
    {
        $studyMonthsForViews = $study->getStudyDurationPerYearFromIndexesForView();
        $yearWithItsIndexes = $study->getStudyDurationPerYearFromIndexes();

		
		$fundingStructureCounts = $study->getFixedAssetsWithCountsDates(FixedAsset::FFE);
		
        return [
			'fundingStructureCounts'=>$fundingStructureCounts,
            'company'=>$company ,
            'type'=>'create',
            'study'=>$study,
            'model'=>$study ,
            'expenseType'=>HHelpers::getClassNameWithoutNameSpace((new Expense())),
            'title'=>__('General Fixed Assets'),
            'storeRoute'=>route('store.ffe.fixed.assets', ['company'=>$company->id , 'study'=>$study->id]),
            'storeFundingRoute'=>route('store.ffe.funding.structure.fixed.assets', ['company'=>$company->id , 'study'=>$study->id]),
            'monthsWithItsYear' => $yearWithItsIndexes,
            'studyMonthsForViews'=>$studyMonthsForViews,
            'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
            'fixedAssetType'=>FixedAsset::FFE
        ];
    }
    protected function getRepeaterRelations():array
    {
        return [
            'fixedAssets'
        ];
    }
    public function store(Company $company, StoreFixedAssetsRequest $request, Study $study)
    {
        $fixedAssetType = $request->get('fixed_asset_type') ;
		$oldIdsFromDatabase = $study->fixedAssets->where('type',$fixedAssetType)->pluck('id')->toArray();
		$study->storeRepeaterRelations($request, $this->getRepeaterRelations(), $company, ['type'=>$fixedAssetType],$oldIdsFromDatabase);
		$fundingStructureCounts = $study->getFixedAssetsWithCountsDates(FixedAsset::FFE);
		$loanStructure = $study->getLoanStructure($fixedAssetType);
        $isFullyFundedThroughEquity = $request->input('generalFixedAssetsFundingStructure.is_fully_funded_though_equity') ;
		$goToFundingStructure = !$isFullyFundedThroughEquity && count($fundingStructureCounts);
		if($isFullyFundedThroughEquity && $loanStructure){
			 $loanStructure->delete();
		}
		$study->recalculateFixedAssets($fixedAssetType);
        if ($goToFundingStructure) {
            return response()->json([
            'redirectTo'=>route('create.ffe.funding.structure.fixed.assets', ['company'=>$company->id,'study'=>$study->id])
        ]);
        }
        $redirectRoute = $study->getFixedAssetNextRoute();
        return response()->json([
            'redirectTo'=>$redirectRoute
        ]);
        
    }
	 public function storeFunding(Company $company, Request $request, Study $study)
    {
        $fixedAssetType = $request->get('fixed_asset_type') ;
        $study->storeRelationsWithNoRepeater($request, $company);
		$study->recalculateFixedAssets($fixedAssetType);
        $redirectRoute = $study->getFixedAssetNextRoute();
        return response()->json([
            'redirectTo'=>$redirectRoute
        ]);
        
    }
	 
}
