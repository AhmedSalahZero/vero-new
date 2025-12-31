<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HArr;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Consolidation;
use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;

class ConsolidationIncomeStatementController extends Controller
{
    public function index(Company $company, Request $request,Consolidation $consolidation)
    {
		$studyIds = $consolidation->study_ids ;
		$onlyViewVars= true;
		$subIncomeStatements = [];
		$mergedResult=[];
		$studyMonthsForViews = [];
		$company = getCurrentCompany();
		$studies =[];
		$defaultClasses  = [];
		foreach($studyIds as $studyId){
			$study = Study::find($studyId);
			$studies[]=$study;
			$currentResult = (new IncomeStatementController)->index($company,$study,$onlyViewVars);
			$currentIncomeStatement  = $currentResult['tableDataFormatted'];
			$currentStudyMonthsForViews = $currentResult['studyMonthsForViews'];
			$defaultClasses = $currentResult['defaultClasses'];
			// $fi = $currentResult['financialYearEndMonthNumber'];
			if(count($currentStudyMonthsForViews) > count($studyMonthsForViews)){
				$studyMonthsForViews = $currentStudyMonthsForViews;
			}
			$subIncomeStatements[]=$currentIncomeStatement;
			$mergedResult = HArr::deepMergeAndSum($mergedResult,$currentIncomeStatement);
			/**
			 * * subitems in  $mergedResult has not effect ignore them
			 */
		}
        return view('non_banking_services.income-statement.multiple.multi-incomestatement',[
			'company'=>$company,
			'studyMonthsForViews'=>$studyMonthsForViews,
			'totalResults'=>$mergedResult,
			'subIncomeStatements'=>$subIncomeStatements,
			'studies'=>$studies,
			'title'=>__('Consolidated Income Statement'),
			'tableTitle'=>__('Consolidated Income Statement'),
			'financialYearEndMonthNumber'=>12,
			'defaultClasses'=>$defaultClasses
		]);
		
	}
}
