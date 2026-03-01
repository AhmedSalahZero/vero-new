<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreDirectFactoringRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\DirectFactoringBreakdown;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class SpreadsheetController extends Controller
{
	use NonBankingService ;
	public function create(Company $company , Request $request,Study $study){
		$viewVars =  [
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
			'title'=>__('Direct Factoring Revenue Stream Breakdown'),
		];
		return view( 'non_banking_services.spread-sheet.form', $viewVars);
	}
	public function getRepeaterRelations():array 
	{
		return [
			'directFactoringBreakdowns'
		];
	}
	
	public function getOldData(Company $company , Request $request , Study $study)
	{
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths); 
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		$datesAsIndexes = array_keys($yearOrMonthsIndexes) ;
		$categories = factoringDueInDays() ;
		$directFactoringBreakdowns = [];
		foreach(count($study->directFactoringBreakdowns) ? $study->directFactoringBreakdowns : [null] as $index=>$directFactoringBreakdown){
				$directFactoringBreakdowns[]=DirectFactoringBreakdown::getRow($directFactoringBreakdown,$datesAsIndexes,$categories);
		}

		$hasEnteredDirectFactoringBreakdown = $study->directFactoringBreakdowns->count();
		$directFactoringRevenueProjection = $study->directFactoringRevenueProjectionByCategory;
		$directFactoringRevenueProjection = $directFactoringRevenueProjection ? $directFactoringRevenueProjection->direct_factoring_transactions_projections  : array_fill_keys($datesAsIndexes,0);
	
		$loanAmountsPerRevenueStreamBreakdown = [];
		$subNames = [];
		$eclRates = [];
		$adminFeesRates = [];
		$equityFundingRates = [];
		$equityFundingValues = [];
		$newLoansFundingRates = [];
		$newLoanFundingValues = [];
		$netDisbursements= [];
		$eclAndNewPortfolioFundingRate = $study->getEclAndNewPortfolioFundingRatesForStreamType(Study::DIRECT_FACTORING) ;
		foreach($yearOrMonthsIndexes as $dateAsIndex => $dateFormatted ){
			$adminFeesRates[$dateAsIndex]=$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getAdminFeesRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$eclRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEclRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$equityFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoansFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoanFundingValues[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingValuesAtYearOrMonthIndex($dateAsIndex):0;
			$netDisbursements[$dateAsIndex]=$study->getTotalDirectFactoringNewPortfolioAmountsAtYearOrMonthIndex($dateAsIndex)['sum']??0;
		}
		return [
			'submitUrl'=>routeWithQueryParam(route('store.direct.factoring.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			'dates'=>(object)$yearOrMonthsIndexes,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			'hasEnteredDirectFactoringBreakdown'=>$hasEnteredDirectFactoringBreakdown,
			'empty_rows'=>[
				'directFactoringBreakdowns'=>DirectFactoringBreakdown::getRow(null,$datesAsIndexes,$categories)
			],
			'model'=>[
				'directFactoringRevenueProjectionByCategory'=>[
					'direct_factoring_transactions_projections'=>$directFactoringRevenueProjection
				],
				'netDisbursements'=>$netDisbursements,
				'directFactoringBreakdowns'=>$directFactoringBreakdowns,
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
	
	public function store(Company $company , StoreDirectFactoringRevenueStreamRequest $request,Study $study)
	{
	
		return response()->json([
			'redirectTo'=>route('create.direct.factoring.revenue.stream.breakdown',['company'=>$company->id,'study'=>$study->id]) 
		]);
		
	}
}
