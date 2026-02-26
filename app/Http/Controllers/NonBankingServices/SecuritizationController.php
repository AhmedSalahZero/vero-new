<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class SecuritizationController extends Controller
{
    use NonBankingService ;
    public function create(Company $company, Request $request, Study $study)
    {
        return view('non_banking_services.securitization.form', $this->getViewVars($company, $study));
    }
    protected function getViewVars(Company $company, Study $study)
    {
        $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
        $isYearsStudy = !$study->isMonthlyStudy();
		$studyMonthsForViews =array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) ;
		$dateIndexWithDate = $study->getDateIndexWithDate();
		$securitizationCalculations = $study->calculateSecuritizationLoans();
        return [
			'securitizationCalculations'=>$securitizationCalculations,
			'dateIndexWithDate'=>$dateIndexWithDate,
            'company'=>$company ,
            'model'=>$study ,
			'study'=>$study,
			'expenseType'=>'securitization',
			'revenueStreamTypes'=> [
				[
					'title'=>__('Leasing'),
					'value'=>Study::LEASING
				],
				[
					'title'=>__('Ijara'),
					'value'=>Study::IJARA
				],
				[
					'title'=>__('Microfinance'),
					'value'=>Study::MICROFINANCE
				],
			],
			// 'products'=>$company->getActiveMicrofinanceProducts(),
            'title'=>__('Securitization'),
            'storeRoute'=>route('store.securitization', ['company'=>$company->id , 'study'=>$study->id]),
            'yearsWithItsMonths' =>$yearsWithItsMonths,
            'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
            'isYearsStudy'=>$isYearsStudy,
	//		'departments'=>$departments,
			'studyMonthsForViews'=>$studyMonthsForViews,
		    'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
        ];
    }

    public function store(Company $company, Request $request, Study $study )
    {
		$study->storeRepeaterRelations($request,['securitizations'],$company,[]);
		 $study->calculateSecuritizationLoans();
		 $securitizations = $request->get('securitizations',[]) ;
		if($request->get('save') == 'save_and_next' || !count($securitizations)){
						return response()->json([
                'redirectTo'=>route('view.manpower.for.non.banking', ['company'=>$company->id,'study'=>$study->id])
            ]);
		}
		return response()->json([
                'redirectTo'=>route('create.securitization', ['company'=>$company->id,'study'=>$study->id])
            ]);
    }
	
	
}
