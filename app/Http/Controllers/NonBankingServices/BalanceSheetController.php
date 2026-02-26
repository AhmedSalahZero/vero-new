<?php

namespace App\Http\Controllers\NonBankingServices;


use App\Exports\NonBankingServices\IncomeStatementExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
	use NonBankingService ;
	
	
	public function view(Request $request , Company $company,Study $study)
	{
		  return view(
            'non_banking_services.income-statement.cash-flow',
			$study->getBalanceSheetViewVars()
        );
	}
	public function exportReport(Request $request,Company $company , Study $study )
	{
		$dates = $study->getStudyDurationPerYearFromIndexesForView();
		$formattedData = $study->getBalanceSheetViewVars()['tableDataFormatted'];
		$reportType = 'Balance Sheet Report';
		$studyName = $study->getName();
		return (new IncomeStatementExport(collect($formattedData),$dates,$studyName,$reportType))->download();
	}
	
}
