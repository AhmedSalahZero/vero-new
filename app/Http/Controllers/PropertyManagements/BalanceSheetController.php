<?php

namespace App\Http\Controllers\PropertyManagements;


use App\Exports\PropertyManagements\IncomeStatementExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
	use PropertyManagement ;
	
	
	public function view(Request $request , Company $company,Study $study)
	{
		  return view(
            'property_managements.income-statement.cash-flow',
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
