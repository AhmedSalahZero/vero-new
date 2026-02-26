<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\ExistingBranch;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class ByBranchesMicrofinanceControllerController extends Controller
{
    use NonBankingService ;
    public function create(Company $company, Request $request, Study $study)
    {
        return view('non_banking_services.microfinance.by-branch-form', $this->getViewVars($company, $study));
    }
    protected function getViewVars(Company $company, Study $study)
    {
        $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
        $isYearsStudy = !$study->isMonthlyStudy();
		$studyMonthsForViews =array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) ;
		$dateIndexWithDate = $study->getDateIndexWithDate();
	//	$salesProjects = $study->microfinanceProductSalesProjects ;
		$microfinanceBranchIds = $study->microfinance_branch_ids?:[];
		$branches =ExistingBranch::whereIn('id',$microfinanceBranchIds)->get();
        return [
			'dateIndexWithDate'=>$dateIndexWithDate,
			'branches'=>$branches,
            'company'=>$company ,
            'model'=>$study ,
			'study'=>$study,
			'products'=>$company->getActiveMicrofinanceProducts(),
            'title'=>__('Microfinance Branches'),
            'storeRoute'=>route('store.loan.microfinance', ['company'=>$company->id , 'study'=>$study->id]),
            'yearsWithItsMonths' =>$yearsWithItsMonths,
            'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
            'isYearsStudy'=>$isYearsStudy,
			'studyMonthsForViews'=>$studyMonthsForViews,
		    'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
        ];
    }


    // public function store(Company $company, Request $request, Study $study )
    // {
		
	// 	return response()->json([
    //             'redirectTo'=>route('view.manpower.for.non.banking', ['company'=>$company->id,'study'=>$study->id])
    //         ]);
    // }
	
}
