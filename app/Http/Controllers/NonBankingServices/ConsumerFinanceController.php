<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAllBranchesMicrofinanceRequest;
use App\Http\Requests\StoreConsumerfinanceRequest;
use App\Http\Requests\StoreNewBranchesMicrofinanceRequest;
use App\Models\Company;
use App\Models\NonBankingService\ExistingBranch;
use App\Models\NonBankingService\Study;
use App\ReadyFunctions\ConvertFlatRateToDecreasingRate;
use App\Traits\NonBankingService;
use Arr;
use Illuminate\Http\Request;

class ConsumerFinanceController extends Controller
{
    use NonBankingService ;
	
		
    public function create(Company $company, Request $request, Study $study ,  $existingBranchId = null )
    {
        return view('non_banking_services.consumer-finance.form', $this->getViewVars($company, $study,$existingBranchId));
    }
    protected function getViewVars(Company $company, Study $study )
    {
        $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
        $isYearsStudy = !$study->isMonthlyStudy();
		$studyMonthsForViews =array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) ;
		$dateIndexWithDate = $study->getDateIndexWithDate();
		 $eclAndNewPortfolioFundingRate = $study?  $study->getEclAndNewPortfolioFundingRatesForStreamType(Study::CONSUMER_FINANCE) : null;
		$title =  __('Consumer Finance'); 
        return [
			'dateIndexWithDate'=>$dateIndexWithDate,
			'eclAndNewPortfolioFundingRate'=>$eclAndNewPortfolioFundingRate, 
            'company'=>$company ,
            'model'=>$study ,
			'study'=>$study,
			'products'=>$company->activeConsumerfinanceProducts,
            'title'=>$title,
            'storeRoute'=>route('store.consumer.finance', ['company'=>$company->id , 'study'=>$study->id]),
            'yearsWithItsMonths' =>$yearsWithItsMonths,
            'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
            'isYearsStudy'=>$isYearsStudy,
			'studyMonthsForViews'=>$studyMonthsForViews,
		    'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
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

    public function store(Company $company, StoreConsumerfinanceRequest $request, Study $study  )
    {

		$isMonthlyStudy = $study->isMonthlyStudy();
		
	
		$study->storeRepeaterRelations($request,['consumerfinanceProductSalesProjects'],$company);
		 $salesProjects =$study->consumerfinanceProductSalesProjects ;
		 $salesProjectsPerFundedBy= [];
        foreach ($salesProjects as $salesProject) {
            $fundedBy = $salesProject->funded_by;
            $monthlyLoanAmounts = $salesProject->monthly_loan_amounts?:[];
            foreach ($monthlyLoanAmounts as $dateAsIndex => $monthlyLoanAmount) {
                $salesProjectsPerFundedBy[$fundedBy][$dateAsIndex] =  isset($salesProjectsPerFundedBy[$fundedBy][$dateAsIndex]) ? $salesProjectsPerFundedBy[$fundedBy][$dateAsIndex] + $monthlyLoanAmount:$monthlyLoanAmount;
            }
        }
		
		
		$totalMonthlyLoanPerMtls = $salesProjectsPerFundedBy['by-mtls']??[];
		$totalMonthlyLoanPerOdas = $salesProjectsPerFundedBy['by-odas']??[];
		$fundedRatesForOdas = $request->input('new_loans_funding_rates.by-odas', []);
		$fundedRatesForByMtls = $request->input('new_loans_funding_rates.by-mtls', []);
		foreach($totalMonthlyLoanPerMtls as $monthIndex =>  &$value){
			$yearIndexOrMonthIndex = $isMonthlyStudy ? $monthIndex : $study->getYearIndexFromDateIndex($monthIndex); 
			 $currentRate = $fundedRatesForByMtls[$yearIndexOrMonthIndex] / 100;
			 $value = $value * $currentRate ;
		}
		foreach($totalMonthlyLoanPerOdas as $monthIndex =>  &$value){
			$yearIndexOrMonthIndex = $isMonthlyStudy ? $monthIndex : $study->getYearIndexFromDateIndex($monthIndex); 
			 $currentRate = $fundedRatesForOdas[$yearIndexOrMonthIndex] / 100;
			 $value = $value * $currentRate ;
		}
		$study->storeAdminFeesAndFundingStructureFor($request, Study::CONSUMER_FINANCE,[],[],$totalMonthlyLoanPerMtls,$totalMonthlyLoanPerOdas);
		 $study->calculateConsumerfinanceLoans();
   
	   $study->updateExpensesPercentageAndCostPerUnitsOfSales();
	   
	   
		return response()->json([
                'redirectTo'=>$study->getRevenueRoute(Study::SECURITIZATION)
            ]);
    }
	
}
