<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PropertyManagement\GeneralAndReserveAssumption;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Illuminate\Http\Request;

class GeneralAndReservationAssumptionController extends Controller
{
	use PropertyManagement ;
	public function create(Company $company , Request $request,Study $study){
		
		return view('property_managements.general-assumption.form', $this->getViewVars($company,$study));
	}
	public function getOldData(Company $company , Request $request , Study $study)
	{
		$study->load(['generalAndReserveAssumption']);
		$dates =array_map(function($date){
			return formatDateForView($date);
		},array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) );
		$yearIndexes = $study->getYearlyIndexes();
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths); 
		$generalAndReserveAssumption = $study->generalAndReserveAssumption;
		// $cbeCorridorChangesRates = $study->getCbeCorridorChangesRatesFormatted($dates) ;
		// $cbeBaseLendingCorridorRates = $study->getCbeBaseLendingCorridorRatesFormatted($dates) ;
		$cbeLendingCorridorRates = $study->getCbeLendingCorridorRatesFormatted($dates) ;
		$creditInterestRateForSurplusCashes = $study->getCreditInterestRateForSurplusCashesFormatted($dates) ;
		

		return [
			'submitUrl'=>route('property.management.store.general.assumption',['company'=>$company->id , 'study'=>$study->id]),
			'dates'=>(object)$dates,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			// 'hasLeasing'=>$study->hasLeasing(),
			// 'hasFactoring'=>$study->hasDirectFactoring() || $study->hasReverseFactoring(),
			// 'hasMortgage'=>$study->hasIjaraMortgage() || $study->hasPortfolioMortgage(),
			// 'hasMicrofinance'=>$study->hasMicroFinance(),
			// 'hasConsumerFinance'=>$study->hasConsumerFinance(),
			'years'=>$yearIndexes,
			'model'=>[
				'legal_reserve_rate'=>$generalAndReserveAssumption ?$generalAndReserveAssumption->getLegalReserveRate() : 0,
				'max_legal_reserve_rate'=>$generalAndReserveAssumption ?$generalAndReserveAssumption->getMaxLegalReserveRate() : 0,
				// 'financial_regulatory_authority_rate'=>$generalAndReserveAssumption ?$generalAndReserveAssumption->getFinancialRegulatoryAuthorityRate() : 0,
				// 'max_financial_regulatory_authority_rate'=>$generalAndReserveAssumption ?$generalAndReserveAssumption->getMaxFinancialRegulatoryAuthorityRate() : 0,
				'cbe_lending_corridor_rates'=>$cbeLendingCorridorRates,
				// 'cbe_lending_corridor_rates'=>$study->getCbeLendingCorridorRatesFormatted($dates),
				'credit_interest_rate_for_surplus_cash'=>$creditInterestRateForSurplusCashes,
				'bank_lending_margin_rates'=>$study->getBankLendingMarginRatesFormatted($dates),
				'odas_bank_lending_margin_rates'=>$study->getOdasBankLendingMarginRatesFormatted($dates),
				'employee_profit_share_rates'=>$study->getEmployeeProfitShareRatesFormatted($yearIndexes),
				'border_of_directors_profit_share_rates'=>$study->getBorderOfDirectorsProfitShareRatesFormatted($yearIndexes),
				'shareholders_first_dividend_portions'=>$study->getShareholdersFirstDividendPortionsFormatted($yearIndexes),
				'shareholders_dividend_payout_ratios'=>$study->getShareholdersDividendPaymentRatiosFormatted($yearIndexes),
				// 'shareholders_dividend_in_cash_or_shares'=>$study->getShareholdersDividedInCashOrSharesFormatted($yearIndexes),
				// 'from_dispersement_of'=>$generalAndReserveAssumption ? $generalAndReserveAssumption->getFromDispersementOf():1,
				// 'from_dispersement_of_rates'=>$study->getFromDispersementOfRates($dates),
				'to_cover_cost'=>$generalAndReserveAssumption ? $generalAndReserveAssumption->getToCoverCostOf():1,
				'to_cover_cost_rates'=>$study->getToCoverCostRates($dates),
				'min_cash_balances'=>$study->getMinCashBalancesFormatted($dates),
				'study_id'=>$study->id
				],
		];
	}
	
	protected function getViewVars(Company $company, Study $study){
		// $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		// $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		// $yearIndexes = $study->getYearlyIndexes();
		// $isYearsStudy = !$study->isMonthlyStudy();
		return [
			'company'=>$company ,
			'study'=>$study,
			'title'=>__('General Assumption'),
			
		];
	}
	public function store(Company $company , Request $request,Study $study)
	{
		$data = $request->except(['_token','save','_method']) ;
		$study->generalAndReserveAssumption ? $study->generalAndReserveAssumption->update($data) : GeneralAndReserveAssumption::create($data);
		// $redirectRoute = $study->getRevenueRoute(Study::LEASING) ;
		$redirectRoute = route('property.management.create.occupied.properties.with.full.rent.coverage.duration', ['company'=>$study->company->id,'study'=>$study->id]);
		if($request->get('submit_button') == 'save'){
			return response()->json([
				'status'=>true 
			]);
		}
		return response()->json([
			'redirectTo'=>$redirectRoute
		]);
	}
}
