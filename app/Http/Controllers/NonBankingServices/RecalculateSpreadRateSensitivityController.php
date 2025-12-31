<?php

namespace App\Http\Controllers\NonBankingServices;


use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RecalculateSpreadRateSensitivityController extends Controller
{
	use NonBankingService ;
	
	public function recalculate(Company $company , Study $study,Request $request)
	{

		$sensitivityMarginRates = $request->get('sensitivity_margin_rate',[]);
		$tableNameMapping = [
			'leasingRevenueStreamBreakdown'=>'leasing_revenue_stream_breakdowns',
			'ijaraMortgageBreakdowns'=>'ijara_mortgage_breakdowns',
			'reverseFactoringBreakdowns'=>'reverse_factoring_breakdowns',
		];
		foreach($sensitivityMarginRates as $relationName => $marginRates){
			$tableName = $tableNameMapping[$relationName];
			foreach($marginRates as $revenueStreamBreakdownId => $sensitivityMarginRate){
				DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($tableName)->where('id',$revenueStreamBreakdownId)->update([
					'sensitivity_margin_rate'=>number_unformat($sensitivityMarginRate)
				]);
			}
		}
		$study->recalculateAllRevenuesLoans($request,true);
		// $study->storeFixedLoans(Study::LEASING,'leasingRevenueStreamBreakdown',true);
		// $study->storeFixedLoans(Study::IJARA,'ijaraMortgageBreakdowns',true);
		// $study->storeVariableLoans(Study::REVERSE_FACTORING,'reverseFactoringBreakdowns',true);
		// $study->updateExpensesPercentageAndCostPerUnitsOfSales(true);
		return redirect()->route('view.results.dashboard.with.sensitivity',['company'=>$company->id,'study'=>$study->id]);
	}
}
