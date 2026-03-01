<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreIjaraMortgageRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\IjaraMortgageBreakdown;
use App\Models\NonBankingService\IjaraMortgageRevenueStreamBreakdown;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class IjaraMortgageController extends Controller
{
	use NonBankingService ;
	public function getModel():IjaraMortgageRevenueStreamBreakdown
	{
		return new IjaraMortgageRevenueStreamBreakdown();
	}
	public function create(Company $company , Request $request,Study $study){
		$model = $this->getModel();
		return view($model->getFormName(), $this->getModel()->getViewVars($company,$study));
	}
	public function getRepeaterRelations():array 
	{
		return [
			'ijaraMortgageBreakdowns'
		];
	}
	public function getOldData(Company $company , Request $request , Study $study)
	{
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths); 
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		$datesAsIndexes = array_keys($yearOrMonthsIndexes) ;
		$installmentIntervals = [['title'=>__('Monthly'),'id'=>'monthly'],['title'=>__('Quarterly'),'id'=>'quarterly'],['id'=>'semi annually','title'=>__('Semi-annually')]] ;
		$ijaraMortgageBreakdowns = [];
		foreach(count($study->ijaraMortgageBreakdowns) ? $study->ijaraMortgageBreakdowns : [null] as $index=>$ijaraMortgageBreakdown){
				$ijaraMortgageBreakdowns[]=IjaraMortgageBreakdown::getRow($ijaraMortgageBreakdown,$datesAsIndexes);
		}
		$ijaraMortgageRevenueProjection = $study->ijaraMortgageRevenueProjectionByCategory;
		$ijaraMortgageRevenueProjection = $ijaraMortgageRevenueProjection ? $ijaraMortgageRevenueProjection->ijara_mortgage_transactions_projections  : array_fill_keys($datesAsIndexes,0);
		$loanAmountsPerRevenueStreamBreakdown = [];
		$subNames = [];
		$eclRates = [];
		$adminFeesRates = [];
		$equityFundingRates = [];
		$equityFundingValues = [];
		$newLoansFundingRates = [];
		$newLoanFundingValues = [];
		$eclAndNewPortfolioFundingRate =  $study->getEclAndNewPortfolioFundingRatesForStreamType(Study::IJARA) ;
		foreach($yearOrMonthsIndexes as $dateAsIndex => $dateFormatted ){
			$adminFeesRates[$dateAsIndex]=$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getAdminFeesRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$eclRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEclRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$equityFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoansFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoanFundingValues[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingValuesAtYearOrMonthIndex($dateAsIndex):0;
		}
		return [
			'submitUrl'=>routeWithQueryParam(route('store.ijara.mortgage.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			'dates'=>(object)$yearOrMonthsIndexes,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			'empty_rows'=>[
				'ijaraMortgageBreakdowns'=>IjaraMortgageBreakdown::getRow(null,$datesAsIndexes)
			],
			'model'=>[
				'ijaraMortgageRevenueProjectionByCategory'=>[
					'ijara_mortgage_transactions_projections'=>$ijaraMortgageRevenueProjection
				],
				'ijaraMortgageBreakdowns'=>$ijaraMortgageBreakdowns,
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
				'installmentIntervals'=>$installmentIntervals
			]
		];
	}
	
	public function store(Company $company , StoreIjaraMortgageRevenueStreamRequest $request,Study $study)
	{
	//	$study->storeRelationsWithNoRepeater($request,$company,['seasonality']);
		$study->storeRepeaterRelations($request,$this->getRepeaterRelations(),$company);
	//	$study->syncSeasonality($request->get('seasonality',[]),Study::IJARA , $company->id );
		$study->storeFixedLoans($request,Study::IJARA,'ijaraMortgageBreakdowns');
		$study->updateExpensesPercentageAndCostPerUnitsOfSales();
		if($request->get('save_button') == 'save'){
			return response()->json([
				'redirectTo'=>route('create.ijara.mortgage.revenue.stream.breakdown',['company'=>$company->id,'study'=>$study->id])
			]);
		}
		return response()->json([
			'redirectTo'=>$study->getRevenueRoute(Study::PORTFOLIO_MORTGAGE)
		]);
	}
}
