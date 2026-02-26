<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\ExistingBranch;
use App\Models\NonBankingService\Study;
use App\ReadyFunctions\ConvertFlatRateToDecreasingRate;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class MicrofinanceLoanController extends Controller
{
    use NonBankingService ;
    public function create(Company $company, Request $request, Study $study , $branchId = null )
    {
        return view('non_banking_services.microfinance.loan-form', $this->getViewVars($company, $study,$branchId));
    }
    protected function getViewVars(Company $company, Study $study,$branchId = null)
    {
        $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
        $isYearsStudy = !$study->isMonthlyStudy();
        $studyMonthsForViews =array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) ;
        $dateIndexWithDate = $study->getDateIndexWithDate();
        $salesProjects =$branchId ? $study->microfinanceProductSalesProjects->where('branch_id',$branchId)  :$study->microfinanceProductSalesProjects ;
	
        $salesProjectsPerProducts= [];
        $salesProjectsPerFundedBy= [];
        $salesProjectsPerTypes= [];
        foreach ($salesProjects as $salesProject) {
            $productId = $salesProject->microfinance_product_id;
            $fundedBy = $salesProject->funded_by;
            $type = $salesProject->type;
            $monthlyLoanAmounts = $salesProject->monthly_loan_amounts?:[];
			
			$yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
            foreach ($monthlyLoanAmounts as $dateAsIndex => $monthlyLoanAmount) {
				$currentYearOrMonthIndex = $isYearsStudy ? $study->getYearIndexFromDateIndex($dateAsIndex) : $dateAsIndex;
                $salesProjectsPerProducts[$productId][$currentYearOrMonthIndex] =  isset($salesProjectsPerProducts[$productId][$currentYearOrMonthIndex]) ? $salesProjectsPerProducts[$productId][$currentYearOrMonthIndex] + $monthlyLoanAmount:$monthlyLoanAmount;
                $salesProjectsPerFundedBy[$fundedBy][$productId][$currentYearOrMonthIndex] = isset($salesProjectsPerFundedBy[$fundedBy][$productId][$currentYearOrMonthIndex]) ? $salesProjectsPerFundedBy[$fundedBy][$productId][$currentYearOrMonthIndex] + $monthlyLoanAmount   : $monthlyLoanAmount  ;
                $salesProjectsPerFundedBy[$fundedBy]['total'][$currentYearOrMonthIndex] = isset($salesProjectsPerFundedBy[$fundedBy]['total'][$currentYearOrMonthIndex]) ? $salesProjectsPerFundedBy[$fundedBy]['total'][$currentYearOrMonthIndex] + $monthlyLoanAmount   : $monthlyLoanAmount  ;
                $salesProjectsPerTypes[$type][$productId][$currentYearOrMonthIndex] = isset($salesProjectsPerTypes[$type][$productId][$currentYearOrMonthIndex]) ? $salesProjectsPerTypes[$type][$productId][$currentYearOrMonthIndex] + $monthlyLoanAmount   : $monthlyLoanAmount  ;
				$salesProjectsPerTypes[$type]['total'][$currentYearOrMonthIndex] = isset($salesProjectsPerTypes[$type]['total'][$currentYearOrMonthIndex]) ? $salesProjectsPerTypes[$type]['total'][$currentYearOrMonthIndex] + $monthlyLoanAmount : $monthlyLoanAmount;
            }
        }
		$branchName = $branchId ? ExistingBranch::find($branchId)->getName() : '';
        return [
			'branchName'=>$branchName,
            'salesProjectsPerTypes'=>$salesProjectsPerTypes,
            'salesProjectsPerFundedBy'=>$salesProjectsPerFundedBy,
            'salesProjectsPerProducts'=>$salesProjectsPerProducts,
            'dateIndexWithDate'=>$dateIndexWithDate,
            'eclAndNewPortfolioFundingRate'=>$study->getEclAndNewPortfolioFundingRatesForStreamType(Study::MICROFINANCE),
            'company'=>$company ,
            'model'=>$study ,
            'study'=>$study,
            'products'=>$company->getActiveMicrofinanceProducts(),
            'title'=>$branchId ? $branchName. ' '.   __('Loans') : __('Microfinance Loans'),
            'storeRoute'=>routeWithQueryParam(route('store.loan.microfinance', ['company'=>$company->id , 'study'=>$study->id])),
            'yearsWithItsMonths' =>$yearsWithItsMonths,
            'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
            'isYearsStudy'=>$isYearsStudy,
            'studyMonthsForViews'=>$studyMonthsForViews,
            'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
        ];
    }
    public function getDecreaseRateBasedOnFlatRate(Company $company, Request $request, Study $study)
    {
        $flatRate = $request->get('flatRate', 0) ;
        $tenor = $request->get('tenor', 0) ;
        $decreaseRate = (new ConvertFlatRateToDecreasingRate())->excel_rate($flatRate, $tenor);
        $decreaseRate = number_format($decreaseRate, 4) . ' %';
        return response()->json([
            'status'=>true ,
            'decreaseRate'=>$decreaseRate
        ]);
    }

    public function store(Company $company, Request $request, Study $study)
    {
		
		$isMonthlyStudy = $study->isMonthlyStudy();
		 $salesProjects =$study->microfinanceProductSalesProjects ;
	
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
		
		$study->storeAdminFeesAndFundingStructureFor($request, Study::MICROFINANCE,[],[],$totalMonthlyLoanPerMtls,$totalMonthlyLoanPerOdas);
       $study->calculateMicrofinanceLoans();
	   $study->updateExpensesPercentageAndCostPerUnitsOfSales();
        
        return response()->json([
                'redirectTo'=>$study->getRevenueRoute(Study::CONSUMER_FINANCE)
            ]);
    }

}
