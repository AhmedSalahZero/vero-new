<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewBranchesMicrofinanceRequest;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\ReadyFunctions\ConvertFlatRateToDecreasingRate;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class NewBranchesMicrofinanceControllerController extends Controller
{
    use NonBankingService ;
	const BRANCH_TYPE = 'new-branches';
    public function create(Company $company, Request $request, Study $study)
    {
        return view('non_banking_services.microfinance.new-branches-form', $this->getViewVars($company, $study));
    }
    protected function getViewVars(Company $company, Study $study)
    {
        $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
        $isYearsStudy = !$study->isMonthlyStudy();
		$studyMonthsForViews =array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) ;
		$departments = $company->microfinanceDepartments;
		$dateIndexWithDate = $study->getDateIndexWithDate();
        return [
			'dateIndexWithDate'=>$dateIndexWithDate,
            'company'=>$company ,
            'model'=>$study ,
			'study'=>$study,
			'expenseType'=>self::BRANCH_TYPE,
			'products'=>$company->getActiveMicrofinanceProducts(),
            'title'=>__('New Branches Microfinance'),
            'storeRoute'=>route('store.new-branches.microfinance', ['company'=>$company->id , 'study'=>$study->id]),
            'yearsWithItsMonths' =>$yearsWithItsMonths,
            'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
            'isYearsStudy'=>$isYearsStudy,
			'departments'=>$departments,
			'studyMonthsForViews'=>$studyMonthsForViews,
		    'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
		    'type'=>self::BRANCH_TYPE,
		    'manpowerType'=>self::BRANCH_TYPE,
		    'branchPlanningBaseType'=>self::BRANCH_TYPE
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

    public function store(Company $company, StoreNewBranchesMicrofinanceRequest $request, Study $study )
    {
		$newBranchesHiringCounts=[];
		$positions = $request->get('manpowers',[]);
		$openingProjects = $request->get('newBranchMicrofinanceOpeningProjections');
		foreach($positions as $positionId => $positionArr){
			foreach($openingProjects as $currentLoopIndex=>$openingProjectArr){
				$startDateAsString = $openingProjectArr['start_date'].'-01';
				$startDateAsIndex = $study->convertDateStringToDateIndex($startDateAsString);
				foreach($positionArr['hiring_counts'] as $hiringIndex => $hiringCount){
					$currentIndex = $hiringIndex+$startDateAsIndex;
					$currentCount = $hiringCount * $openingProjectArr['counts'];
					$newBranchesHiringCounts[$positionId][$currentIndex] = isset($newBranchesHiringCounts[$positionId][$currentIndex]) ? $newBranchesHiringCounts[$positionId][$currentIndex] +  $currentCount:$currentCount ;
				}
			}
		}
		$study->saveManpowerForm($request,self::BRANCH_TYPE,null,$newBranchesHiringCounts);
		$study->storeRepeaterRelations($request,['newBranchMicrofinanceOpeningProjections'],$company,[]);
		
		
		$oldIds = $study->microfinanceProductSalesProjects->where('type',self::BRANCH_TYPE)->pluck('id')->toArray();
		$study->storeRepeaterRelations($request,['microfinanceProductSalesProjects'],$company,[],$oldIds);
		
		$oldIds = $study->microfinanceLoanOfficerCases->where('type',self::BRANCH_TYPE)->pluck('id')->toArray();
		$study->storeRepeaterRelations($request,['microfinanceLoanOfficerCases'],$company,[],$oldIds);
		
		$study->recalculateMicrofinanceTotalCasesCounts(self::BRANCH_TYPE);
		$accumulatedOpeningBranchesCounts = $study->getNewBranchesOpeningBalancesAccumulation();
		$study->handleFixedRepeatingExpenses($request,$accumulatedOpeningBranchesCounts);
       
		return response()->json([
                'redirectTo'=>route('create.loan.microfinance', ['company'=>$company->id,'study'=>$study->id])
            ]);
    }
	
}
