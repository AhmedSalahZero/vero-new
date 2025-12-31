<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\IncomeStatement;
use Illuminate\Http\Request;

class ExpenseController 
{
	public function create(Company $company)
	{
	
		$model = IncomeStatement::first();
		return view('admin.ready-made-forms.expense',[
			'company'=>$company , 
			'pageTitle'=>__('Marketing Expense Form'),
			'storeRoute'=>route('admin.store.expense',['company'=>$company->id]),
			'type'=>'create',
			'dates'=>$model->getIntervalFormatted()	,
			'category'=>'expense',
			'model'=>$model 
		]);
	}
	public function store($company_id,Request $request){
		
		$modelId = $request->get('model_id');
		$modelName = $request->get('model_name');
		$model = ('\App\Models\\'.$modelName)::find($modelId);
		foreach((array)$request->get('tableIds') as $tableId){
			#::delete all
			$model->generateRelationDynamically($tableId)->delete();
			foreach((array)$request->get($tableId) as  $tableDataArr){
					$tableDataArr['relation_name']  = $tableId ;
					$tableDataArr['company_id']  = $company_id ;
					$tableDataArr['model_id']   = $modelId ;
					$tableDataArr['model_name']   = $modelName ;
					if($tableDataArr['payment_terms'] == 'customize'){
						$tableDataArr['custom_collection_policy'] = sumDueDayWithPayment($tableDataArr['payment_rate '],$tableDataArr['due_days']);
					}
						$model->generateRelationDynamically($tableId)->create($tableDataArr);
					
				
			}
		}
	
		return redirect()->back()->with('success',__('Done'));
		
		
	}
}
