<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Consolidation;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class ConsolidationController extends Controller
{
	use NonBankingService ;
	
	public function create(Company $company , Request $request){
		
		return view('non_banking_services.expense-per-employee.form', $this->getViewVars($company));
	}
	protected function getViewVars(Company $company,$model = null){
		
		return [
			'company'=>$company ,
			'department'=>$model ,
			'title'=>__('Expense Per Employee'),
			'study'=>$model,
			'expenseType'=>'expense_per_employee',
			'model'=>$model,
			'storeRoute'=>route('store.consolidations',['company'=>$company->id,'study'=>$model->id]),
		];
	}
	public function store(Request $request , Company $company )
	{
		
		$data = array_merge($request->except(['token']),[
			'company_id'=>$company->id
		]);
		Consolidation::create($data);
		return redirect()->back();
	}
	


}
