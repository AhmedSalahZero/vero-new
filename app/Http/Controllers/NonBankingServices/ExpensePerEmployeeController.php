<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class ExpensePerEmployeeController extends Controller
{
	use NonBankingService ;
	
	public function create(Company $company , Request $request){
		$study = Study::find($request->segment(5));
		
		return view('non_banking_services.expense-per-employee.form', $this->getViewVars($company,$study));
	}
	protected function getViewVars(Company $company,$model = null){
		$departmentsFormatted = $company->departments->sortBy('name')->pluck('name','id')->toArray() ;
		$departments = [];
		
		foreach($departmentsFormatted as $id=>$title){
			$departments[] = [
				'value'=>$id,
				'title'=>$title
			];
		}
		return [
			'company'=>$company ,
			'department'=>$model ,
			'title'=>__('Expense Per Employee'),
			'departmentsFormatted'=>$departments,
			'study'=>$model,
			'expenseType'=>'expense_per_employee',
			'model'=>$model,
			'storeRoute'=>route('store.expenses',['company'=>$company->id,'study'=>$model->id]),
			// 'storeRoute'=>isset($model) ? route('update.departments',['company'=>$company->id,'department'=>$model->id]) :route('store.departments',['company'=>$company->id]),
		];
	}
	
	// الحسبة هنا
	// ExpensesController

}
