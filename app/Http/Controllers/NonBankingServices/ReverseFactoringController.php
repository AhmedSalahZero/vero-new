<?php

namespace App\Http\Controllers\NonBankingServices;


use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreReverseFactoringRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\ReverseFactoringBreakdown;
use App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;


class ReverseFactoringController extends Controller
{
	use NonBankingService ;
	public function getModel():ReverseFactoringRevenueStreamBreakdown
	{
		return new ReverseFactoringRevenueStreamBreakdown();
	}
	public function create(Company $company , Request $request,Study $study){
		$model = $this->getModel();
		return view($model->getFormName(), $this->getModel()->getViewVars($company,$study));
	}
	public function getRepeaterRelations():array 
	{
		return [
			'reverseFactoringBreakdowns'
		];
	}
	public function getOldData(Company $company , Request $request , Study $study)
	{
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths); 
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		$datesAsIndexes = array_keys($yearOrMonthsIndexes) ;
		$categories = reverseFactoringSelector() ;
		$reverseFactoringBreakdowns = [];
		foreach(count($study->reverseFactoringBreakdowns) ? $study->reverseFactoringBreakdowns : [null] as $index=>$reverseFactoringBreakdown){
				$reverseFactoringBreakdowns[]=ReverseFactoringBreakdown::getRow($reverseFactoringBreakdown,$datesAsIndexes,$categories);
		}

		$hasEnteredReverseFactoringBreakdown = $study->reverseFactoringBreakdowns->count();
		$reverseFactoringRevenueProjection = $study->reverseFactoringRevenueProjectionByCategory;
		$reverseFactoringRevenueProjection = $reverseFactoringRevenueProjection ? $reverseFactoringRevenueProjection->reverse_factoring_transactions_projections  : array_fill_keys($datesAsIndexes,0);
	
		$loanAmountsPerRevenueStreamBreakdown = [];
		$subNames = [];
		$eclRates = [];
		$adminFeesRates = [];
		$equityFundingRates = [];
		$equityFundingValues = [];
		$newLoansFundingRates = [];
		$newLoanFundingValues = [];
		$eclAndNewPortfolioFundingRate = $study?  $study->getEclAndNewPortfolioFundingRatesForStreamType(Study::REVERSE_FACTORING) : null;
		foreach($yearOrMonthsIndexes as $dateAsIndex => $dateFormatted ){
			$adminFeesRates[$dateAsIndex]=$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getAdminFeesRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$eclRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEclRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$equityFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoansFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoanFundingValues[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingValuesAtYearOrMonthIndex($dateAsIndex):0;
		}
		return [
			'submitUrl'=>routeWithQueryParam(route('store.reverse.factoring.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			'dates'=>$yearOrMonthsIndexes,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			'empty_rows'=>[
				'reverseFactoringBreakdowns'=>ReverseFactoringBreakdown::getRow(null,$datesAsIndexes,$categories)
			],
			'model'=>[
				'reverseFactoringRevenueProjectionByCategory'=>[
					'reverse_factoring_transactions_projections'=>$reverseFactoringRevenueProjection
				],

				'reverseFactoringBreakdowns'=>$reverseFactoringBreakdowns,
				'loan_amounts'=>[
					'names'=>$subNames,
					'sub_items'=>$loanAmountsPerRevenueStreamBreakdown,
				],
				'admin_fees'=> $adminFeesRates,
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
	
	public function store(Company $company , StoreReverseFactoringRevenueStreamRequest $request,Study $study)
	{
		//	$study->storeRelationsWithNoRepeater($request,$company,['seasonality']);
			$study->storeRepeaterRelations($request,$this->getRepeaterRelations(),$company);
	//		$study->syncSeasonality($request->get('seasonality',[]),Study::REVERSE_FACTORING , $company->id ) ;
			$study->storeVariableLoans($request,Study::REVERSE_FACTORING,'reverseFactoringBreakdowns');
			$study->updateExpensesPercentageAndCostPerUnitsOfSales();
		return response()->json([
			'redirectTo'=>$study->getRevenueRoute(Study::IJARA)
		]);
	}
}
