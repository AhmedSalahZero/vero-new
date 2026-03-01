<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StorePortfolioMortgageRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class PortfolioMortgageController extends Controller
{
    use NonBankingService ;
    // public function getModel():PortfolioMortgageRevenueStreamBreakdown
    // {
    //     return new PortfolioMortgageRevenueStreamBreakdown();
    // }
    public function create(Company $company, Request $request, Study $study)
    {
   //     $model = $this->getModel();
        // return view('non_banking_services.portfolio-mortgage-revenue-stream-breakdown._delete_form',[
        return view('non_banking_services.portfolio-mortgage-revenue-stream-breakdown.form',[
		//	'eclAndNewPortfolioFundingRate'=>$study->getEclAndNewPortfolioFundingRatesForStreamType(Study::PORTFOLIO_MORTGAGE),
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
	//		'portfolioMortgageEclAndNewPortfolioFundingRate'=>$portfolioMortgageEclAndNewPortfolioFundingRate,
			'title'=>__('Portfolio Mortgage Revenue Stream Breakdown'),
			'storeRoute'=>routeWithQueryParam(route('store.portfolio.mortgage.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			// 'yearsWithItsMonths' => $yearsWithItsMonths,
			// 'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
			// 'isYearsStudy'=>$isYearsStudy
			
		] );
    }

    protected function getRepeaterRelations():array
    {
        return [
            'portfolioMortgageRevenueProjectionByCategories'
        ];
    }
	
protected function getPortfolioMortgageDurations():array 
{
	$result = [];
	for($i = 5 ; $i <= 10 ; $i++){
		$result[] = [
			'title'=>$i . ' ' . __('Years'),
			'id'=>$i
		];
	}
	return $result;
}
	public function getOldData(Company $company , Request $request , Study $study)
	{
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths); 
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		$datesAsIndexes = array_keys($yearOrMonthsIndexes) ;
		$categories = $this->getPortfolioMortgageDurations() ;
		$portfolioMortgageRevenueProjectionByCategories = [];
		foreach(count($study->portfolioMortgageRevenueProjectionByCategories) ? $study->portfolioMortgageRevenueProjectionByCategories : [null] as $index=>$portfolioMortgageRevenueProjectionByCategory){
				$portfolioMortgageRevenueProjectionByCategories[]=PortfolioMortgageRevenueProjectionByCategory::getRow($portfolioMortgageRevenueProjectionByCategory,$datesAsIndexes,$categories);
		}
//		$hasEnteredDirectFactoringBreakdown = $study->directFactoringBreakdowns->count();
		// $directFactoringRevenueProjection = $study->directFactoringRevenueProjectionByCategory;
		// $directFactoringRevenueProjection = $directFactoringRevenueProjection ? $directFactoringRevenueProjection->direct_factoring_transactions_projections  : array_fill_keys($datesAsIndexes,0);
	
		$loanAmountsPerRevenueStreamBreakdown = [];
		$subNames = [];
		$eclRates = [];
		$adminFeesRates = [];
		$equityFundingRates = [];
		$equityFundingValues = [];
		$newLoansFundingRates = [];
		$newLoanFundingValues = [];
	//	$netDisbursements= [];
		$eclAndNewPortfolioFundingRate =  $study->getEclAndNewPortfolioFundingRatesForStreamType(Study::PORTFOLIO_MORTGAGE);
		foreach($yearOrMonthsIndexes as $dateAsIndex => $dateFormatted ){
			$adminFeesRates[$dateAsIndex]=$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getAdminFeesRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$eclRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEclRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$equityFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoansFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoanFundingValues[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingValuesAtYearOrMonthIndex($dateAsIndex):0;
		//	$netDisbursements[$dateAsIndex]=$study->getTotalDirectFactoringNewPortfolioAmountsAtYearOrMonthIndex($dateAsIndex)['sum']??0;
		}
		return [
			'submitUrl'=>routeWithQueryParam(route('store.portfolio.mortgage.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			'dates'=>(object)$yearOrMonthsIndexes,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
		//	'hasEnteredDirectFactoringBreakdown'=>$hasEnteredDirectFactoringBreakdown,
			'empty_rows'=>[
				'portfolioMortgageRevenueProjectionByCategories'=>PortfolioMortgageRevenueProjectionByCategory::getRow(null,$datesAsIndexes,$categories)
			],
			'model'=>[
				'portfolioMortgageRevenueProjectionByCategories'=>$portfolioMortgageRevenueProjectionByCategories,
			//	'netDisbursements'=>$netDisbursements,
			//	'directFactoringBreakdowns'=>$directFactoringBreakdowns,
				'loan_amounts'=>[
					'names'=>$subNames,
					'sub_items'=>$loanAmountsPerRevenueStreamBreakdown,
				],
				'admin_fees_rates'=> $adminFeesRates,
				'ecl_rates'=>$eclRates,
				'equity_funding_rates'=>$equityFundingRates,
				'equity_funding_values'=>$equityFundingValues,
				'new_loans_funding_rates'=>$newLoansFundingRates,
				'new_loans_funding_values'=>$newLoanFundingValues
				],
			'selectOptions'=>[
				'categories'=>$categories
			]
		];
	}
	
    public function store(Company $company, StorePortfolioMortgageRevenueStreamRequest $request, Study $study)
    {
        $study->storeRelationsWithNoRepeater($request, $company);
        $study->storeRepeaterRelations($request, $this->getRepeaterRelations(), $company);
    
    
        // question here
		
		$study->recalculatePortfolioMortgage($request);
		
		$study->updateExpensesPercentageAndCostPerUnitsOfSales();
        if($request->get('submit_button') === 'save'){
			return response()->json([
            'redirectTo'=>route('create.portfolio.mortgage.revenue.stream.breakdown',['company'=>$company->id,'study'=>$study->id])
        ]); 
		}
        return response()->json([
            'redirectTo'=>$study->getRevenueRoute(Study::MICROFINANCE)
        ]);
    }
    // public function addNewCategory(Request $request, Company $company, Study $study)
    // {
    //     $study->portfolioMortgageRevenueProjectionByCategories()->create([
    //         'company_id'=>$company->id
    //     ]);
    //     return redirect()->back();
    // }
    // public function deleteCategory(Request $request, Company $company, Study $study, PortfolioMortgageRevenueProjectionByCategory $portfolioMortgageCategory)
    // {
    //     $portfolioMortgageCategoryId= $portfolioMortgageCategory->id ;
    //     $studyId = $study->id;
    //     DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->where('study_id', $studyId)->where('revenue_stream_type', Study::PORTFOLIO_MORTGAGE)->where('revenue_stream_id', $portfolioMortgageCategoryId)->delete();
    //     DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('sensitivity_loan_schedule_payments')->where('study_id', $studyId)->where('revenue_stream_type', Study::PORTFOLIO_MORTGAGE)->where('revenue_stream_id',$portfolioMortgageCategoryId)->delete();
    //     $portfolioMortgageCategory->delete();
    //     return redirect()->back();
    // }
}
