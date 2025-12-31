<?php

namespace App\Http\Controllers\NonBankingServices;


use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashInOutFlowController extends Controller
{
	use NonBankingService ;
	
	
	public function view(Request $request , Company $company,Study $study)
	{
		$basicCashflowStatement = $study->getCashInOutFlowViewVars() ;
		$hasMicrofinanceWithOdas =$basicCashflowStatement['hasMicrofinanceWithOdas']; 
		$odasTitleStatement = __('ODAs Statement') ; // search for it if you changed it
	
			$netCashBeforeWorkingCapital = $basicCashflowStatement['netCashBeforeWorking'];
			$tableDataFormattedForOdas = $study->cashFlowForOdas($netCashBeforeWorkingCapital);
			$tableDataFormatteds = [
				__('Cashflow Statement')=>$basicCashflowStatement['tableDataFormatted']??[],
				$odasTitleStatement=>$tableDataFormattedForOdas,
			];
			
			$leasingEclAndNewPortfolioFundingRates =[];
			foreach($study->getRevenuesTypesWithTitles() as $revenueStreamId => $revenueStreamTitle){
				$loanStructure = $study->getEclAndNewPortfolioFundingRatesForStreamType($revenueStreamId) ;
				if($loanStructure){
					$leasingEclAndNewPortfolioFundingRates[$revenueStreamId] = $loanStructure;
				}
			}
			$cashflowStatementReport=$study->cashflowStatementReport;
			
			
			
			return view(
            'non_banking_services.income-statement.cash-flow-with-odas',
			array_merge(
				$basicCashflowStatement , 
				['tableDataFormatteds'=>$tableDataFormatteds],
				[
					'studyDates'=>$study->getStudyDates(),
					'leasingEclAndNewPortfolioFundingRates'=>$leasingEclAndNewPortfolioFundingRates,
					'hasMicrofinanceWithOdas'=>$hasMicrofinanceWithOdas,
					'odasTitleStatement'=>$odasTitleStatement,
					'cashflowStatementReport'=>$cashflowStatementReport
				]
			)
        );
		// }
		
		//   return view(
        //     'non_banking_services.income-statement.cash-flow',
		// 	$basicCashflowStatement
        // );
	}
	public function saveManualEquityInjection(Request $request, Company $company , Study $study){
		$study->cashflowStatementReport->update([
			'has_manual_equity_injection'=>$request->boolean('has_manual_equity_injection'),
			'manual_equity_injection'=>$request->get('manual_equity_injection')
		]);
		(new IncomeStatementController)->index($company,$study);
		// $study->recalculateCashflowStatement();
			
		return redirect()->back()->with('success',__('Successfully Recalculated'));
	}
	
}
