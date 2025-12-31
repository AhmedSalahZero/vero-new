<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\ExpenseName;
use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;

class ExpenseStatementReportController extends Controller
{
	public function index(Request $request , Company $company,Study $study)
	{
		
		$expenseTypes = [
			'fixed_monthly_repeating_amount'=>__('Fixed Monthly Repeating'),
			'percentage_of_sales'=>__('Percentage Of Sales'),
			'cost_per_unit'=>__('Cost Per Contract'),
			'expense_per_employee'=>__('Expense Per Employee')	,
			'one_time_expense'=>__('One Time Expense')
		];
		
		$expenseCategories = ExpenseName::getCategories($company);
		
		
		return view('non_banking_services.reports.expense-statement.form',[
			'company'=>$company,
			'study'=>$study,
			'expenseTypes'=>$expenseTypes,
			'expenseCategories'=>$expenseCategories
		]);
	}
	public function result(Request $request , Company $company , Study $study)
	{
		$expenseType = $request->get('expense_type');
		$expenseCategorySlug = $request->get('expense_category_id');
		$expenseNameId = $request->get('expense_name_id');
		$expense = Expense::where('model_name','Study')->where('model_id',$study->id)
		->where('relation_name',$expenseType)
		->where('expense_category',$expenseCategorySlug)
		->where('expense_name_id',$expenseNameId)
		->first();
		if(!$expense){
			return back()->with('fail',__('No Data Found'));
		}
		$studyDates = $study->getStudyDates() ;
		$datesAndIndexesHelpers = $study->datesAndIndexesHelpers($studyDates);
		$operationDurationPerYearFromIndexes = $study->getOperationDurationPerYearFromIndexes();
		$dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate']; 
		$dateWithDateIndex=$datesAndIndexesHelpers['dateWithDateIndex']; 
		$statement  = $expense->collection_statements;
		$results = $statement;
		$orderedResult = [];
		foreach($results as $intervalName => $nameWithDateAndValue){
			foreach(['beginning_balance','expense','vat','total_due','payment','withhold_amount','end_balance'] as $key){
				$orderedResult[$intervalName][$key] = $nameWithDateAndValue[$key]??[];
			}
		}
		return view('non_banking_services.reports.expense-statement.report', [
			'company' => $company,
			'results' => $orderedResult,
			'study' => $study,
			'dateWithDateIndex'=>$dateWithDateIndex,
			'dateIndexWithDate'=>$dateIndexWithDate,
			'dates' => $study->getOnlyDatesOfActiveOperation($operationDurationPerYearFromIndexes,$dateIndexWithDate),
			'navigators' => []
		]);
		
	}
}
