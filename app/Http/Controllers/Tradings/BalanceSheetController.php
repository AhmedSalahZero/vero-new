<?php

namespace App\Http\Controllers\Tradings;


use App\Exports\Tradings\IncomeStatementExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Trading\Study;
use App\Traits\Trading;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
	
	
	public function view(Request $request , Company $company,Study $study)
	{
		  return view(
            'trading.income-statement.cash-flow',
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
