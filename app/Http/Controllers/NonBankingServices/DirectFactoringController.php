<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreDirectFactoringRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class DirectFactoringController extends Controller
{
	use NonBankingService ;
	public function create(Company $company , Request $request,Study $study){
		$directFactoringEclAndNewPortfolioFundingRate = $study?  $study->directFactoringEclAndNewPortfolioFundingRate : null;
		
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		$isYearsStudy = !$study->isMonthlyStudy();
		
		$viewVars =  [
			'eclAndNewPortfolioFundingRate'=>$study->getEclAndNewPortfolioFundingRatesForStreamType(Study::DIRECT_FACTORING),
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
			'directFactoringEclAndNewPortfolioFundingRate'=>$directFactoringEclAndNewPortfolioFundingRate,
			'title'=>__('Direct Factoring Revenue Stream Breakdown'),
			'storeRoute'=>routeWithQueryParam(route('store.direct.factoring.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			'yearsWithItsMonths' => $yearsWithItsMonths,
			'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
			'isYearsStudy'=>$isYearsStudy
			
		];
		return view( 'non_banking_services.direct-factoring-revenue-stream-breakdown.form', $viewVars);
	}
	public function getRepeaterRelations():array 
	{
		return [
			'directFactoringBreakdowns'
		];
	}
	
	public function store(Company $company , StoreDirectFactoringRevenueStreamRequest $request,Study $study)
	{
			
			$studyHasDirectFactoringBreakdowns = $study->refresh()->directFactoringBreakdowns->count(); 
			$reloadPage = $request->get('save') == 'calculate-net-disbursement' ;
			$study->storeRelationsWithNoRepeater($request,$company,['seasonality']);
			$study->storeRepeaterRelations($request,$this->getRepeaterRelations(),$company);
			$study->syncSeasonality($request->get('seasonality',[]),Study::DIRECT_FACTORING , $company->id );
			// دا بيس
			
			$study->refreshDirectFactoringLoans($request);
			
			$study->updateExpensesPercentageAndCostPerUnitsOfSales();
			if($reloadPage){
				return response()->json([
					'redirectTo'=>route('create.direct.factoring.revenue.stream.breakdown',['company'=>$company->id,'study'=>$study->id]) 
				]);
			}
		
			
		if($studyHasDirectFactoringBreakdowns){
			return response()->json([
				'redirectTo'=>$study->getRevenueRoute(Study::REVERSE_FACTORING)
			]);	
		}
		return response()->json([
			'redirectTo'=>route('create.direct.factoring.revenue.stream.breakdown',['company'=>$company->id,'study'=>$study->id]) 
		]);
		
	}
}
