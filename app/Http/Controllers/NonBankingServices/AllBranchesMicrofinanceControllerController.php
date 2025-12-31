<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAllBranchesMicrofinanceRequest;
use App\Http\Requests\StoreNewBranchesMicrofinanceRequest;
use App\Models\Company;
use App\Models\NonBankingService\ExistingBranch;
use App\Models\NonBankingService\Study;
use App\ReadyFunctions\ConvertFlatRateToDecreasingRate;
use App\Traits\NonBankingService;
use Arr;
use Illuminate\Http\Request;

class AllBranchesMicrofinanceControllerController extends Controller
{
    use NonBankingService ;
	public function getBranchType($existingBranchId):string{
		if($existingBranchId){
			return 'by-branch';
		}
		return 'all-branches';
	}
		
    public function create(Company $company, Request $request, Study $study ,  $existingBranchId = null )
    {
        return view('non_banking_services.microfinance.all-branches-form', $this->getViewVars($company, $study,$existingBranchId));
    }
    protected function getViewVars(Company $company, Study $study ,  $existingBranchId  = null )
    {
        $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
        $isYearsStudy = !$study->isMonthlyStudy();
		$studyMonthsForViews =array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) ;
		$departments = $company->microfinanceDepartments;
		$dateIndexWithDate = $study->getDateIndexWithDate();
		$branch = ExistingBranch::find($existingBranchId);
		$branchType = $this->getBranchType($existingBranchId);
		$title = $branchType == 'by-branch' ? $branch->getName() : __('All Branches Microfinance'); 
		$isByBranch = $branchType == 'by-branch';
        return [
			'isByBranch'=>$isByBranch,
			'branchId'=>$existingBranchId,
			'dateIndexWithDate'=>$dateIndexWithDate,
            'company'=>$company ,
            'model'=>$study ,
			'study'=>$study,
			'expenseType'=>$branchType,
			'products'=>$company->getActiveMicrofinanceProducts(),
            'title'=>$title,
            'storeRoute'=>route('store.all-branches.microfinance', ['company'=>$company->id , 'study'=>$study->id,'branch_id'=>$existingBranchId]),
            'yearsWithItsMonths' =>$yearsWithItsMonths,
            'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
            'isYearsStudy'=>$isYearsStudy,
			'departments'=>$departments,
			'studyMonthsForViews'=>$studyMonthsForViews,
		    'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
		    'type'=>$branchType,
		    'manpowerType'=>$branchType,
		    'branchPlanningBaseType'=>$branchType
        ];
    }
	public function getDecreaseRateBasedOnFlatRate(Company $company,Request $request,Study $study)
	{
		$flatRate = $request->get('flatRate',0) ;
		$tenor = $request->get('tenor',0) ;
		$decreaseRate = (new ConvertFlatRateToDecreasingRate())->excel_rate($flatRate,$tenor);
		$decreaseRate = number_format($decreaseRate , 4) . ' %';
		return response()->json([
			'status'=>true ,
			'decreaseRate'=>$decreaseRate
		]);
	}

    public function store(Company $company, StoreAllBranchesMicrofinanceRequest $request, Study $study , int $branchId = null  )
    {
		$branchType = $this->getBranchType($branchId);
		$study->saveManpowerForm($request,$branchType,$branchId);

		$oldIds = $study->microfinanceProductSalesProjects->where('type',$branchType)->where('branch_id',$branchId)->pluck('id')->toArray();
		$study->storeRepeaterRelations($request,['microfinanceProductSalesProjects'],$company,['branch_id'=>$branchId],$oldIds);
		
		$oldIds = $study->microfinanceLoanOfficerCases->where('type',$branchType)->where('branch_id',$branchId)->pluck('id')->toArray();
		$study->storeRepeaterRelations($request,['microfinanceLoanOfficerCases'],$company,['branch_id'=>$branchId],$oldIds);
		$study->recalculateMicrofinanceTotalCasesCounts($branchType);
		$study->update([
			'existing_branches_counts'=>$request->get('existing_branches_counts',0)
		]);
		$study->handleFixedRepeatingExpenses($request,[],$branchId);
       $redirectPageRoute = route('create.new-branches.microfinance', ['company'=>$company->id,'study'=>$study->id]) ;
	   $isLastBranch = Arr::last(ExistingBranch::where('company_id',$company->id)->pluck('id')->toArray()) == $branchId;
		if($branchId && !$isLastBranch){
			return response()->json([
                'redirectTo'=>route('create.by-branch.microfinance',['company'=>$company->id,'study'=>$study->id])
            ]);
		}
		return response()->json([
                'redirectTo'=>$redirectPageRoute
            ]);
    }
	
}
