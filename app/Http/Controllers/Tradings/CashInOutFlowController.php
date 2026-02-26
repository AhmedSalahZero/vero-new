<?php

namespace App\Http\Controllers\Tradings;


use App\Exports\Tradings\IncomeStatementExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Trading\Study;
use App\Traits\Trading;
use Illuminate\Http\Request;

class CashInOutFlowController extends Controller
{
	use Trading ;
	
	public function exportReport(Request $request,Company $company , Study $study )
	{
		$dates = $study->getStudyDurationPerYearFromIndexesForView();
		$formattedData = $study->getCashInOutFlowViewVars()['tableDataFormatted'];
		$reportType = 'Cash In Out Flow Report';
		$studyName = $study->getName();
		return (new IncomeStatementExport(collect($formattedData),$dates,$studyName,$reportType))->download();
	}
	
	public function view(Request $request , Company $company,Study $study)
	{
		$basicCashflowStatement = $study->getCashInOutFlowViewVars() ;
	//	$hasMicrofinanceWithOdas =$basicCashflowStatement['hasMicrofinanceWithOdas']; 
		$odasTitleStatement = __('ODAs Statement') ; // search for it if you changed it
	
			// $netCashBeforeWorkingCapital = $basicCashflowStatement['netCashBeforeWorking'];
			$tableDataFormattedForOdas = [];
			// $tableDataFormattedForOdas = $study->cashFlowForOdas($netCashBeforeWorkingCapital);
			$tableDataFormatteds = [
				__('Cashflow Statement')=>$basicCashflowStatement['tableDataFormatted']??[],
				// $odasTitleStatement=>$tableDataFormattedForOdas,
			];
			
			// $leasingEclAndNewPortfolioFundingRates =[];
			// foreach($study->getRevenuesTypesWithTitles() as $revenueStreamId => $revenueStreamTitle){
			// 	$loanStructure = $study->getEclAndNewPortfolioFundingRatesForStreamType($revenueStreamId) ;
			// 	if($loanStructure){
			// 		$leasingEclAndNewPortfolioFundingRates[$revenueStreamId] = $loanStructure;
			// 	}
			// }
			$cashflowStatementReport=$study->cashflowStatementReport;
			
			
			
			return view(
            'tradings.income-statement.cash-flow-with-odas',
			array_merge(
				$basicCashflowStatement , 
				['tableDataFormatteds'=>$tableDataFormatteds],
				[
					'studyDates'=>$study->getStudyDates(),
					// 'leasingEclAndNewPortfolioFundingRates'=>$leasingEclAndNewPortfolioFundingRates,
			//		'hasMicrofinanceWithOdas'=>$hasMicrofinanceWithOdas,
					'odasTitleStatement'=>$odasTitleStatement,
					'cashflowStatementReport'=>$cashflowStatementReport
				]
			)
        );
		// }
		
		//   return view(
        //     'tradings.income-statement.cash-flow',
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
