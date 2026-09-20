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
		  /**
		   * * الفيو بيستخدم $company في لينك التصدير (سطر ٥٨) ، و
		   * * getBalanceSheetViewVars() مابترجعهاش — فالصفحة كانت بترمي
		   * * Undefined variable $company و ترجّع 500 على طول.
		   *
		   * * IncomeStatementController بيبعتها مع نفس الفيو ، عشان كده
		   * * الصفحة التانية شغالة و دي لأ.
		   */
		  return view(
            'non_banking_services.income-statement.cash-flow',
			array_merge($study->getBalanceSheetViewVars(), ['company' => $company])
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
