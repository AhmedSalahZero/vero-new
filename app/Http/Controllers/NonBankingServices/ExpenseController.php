<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreExpenseNamesRequest;
use App\Models\Company;
use App\Models\NonBankingService\ExpenseName;
use App\Traits\NonBankingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
	use NonBankingService ;
	protected function applyFilter(Request $request,Collection $collection):Collection{
		if(!count($collection)){
			return $collection;
		}
		$searchFieldName = $request->get('field');
		$dateFieldName =  'created_at' ; // change it 
		// $dateFieldName = $searchFieldName === 'balance_date' ? 'balance_date' : 'created_at'; 
		$from = $request->get('from');
		$to = $request->get('to');
		$value = $request->query('value');
		$collection = $collection
		->when($request->has('value'),function($collection) use ($request,$value,$searchFieldName){
			return $collection->filter(function($moneyReceived) use ($value,$searchFieldName){
				$currentValue = $moneyReceived->{$searchFieldName} ;
				// if($searchFieldName == 'bank_id'){
				// 	$currentValue = $moneyReceived->getBankName() ;  
				// }
				return false !== stristr($currentValue , $value);
			});
		})
		->when($request->get('from') , function($collection) use($dateFieldName,$from){
			return $collection->where($dateFieldName,'>=',$from);
		})
		->when($request->get('to') , function($collection) use($dateFieldName,$to){
			return $collection->where($dateFieldName,'<=',$to);
		})
		->sortBy('name')->values();
		
		return $collection;
	}
	
    public function index(Company $company , Request $request){
		
		$expenseCategories = ExpenseName::where('company_id',$company->id)->pluck('expense_type')->unique()->toArray();
		$items = [];
	
			foreach($expenseCategories as $index=>$expenseCategoryId){
				$items[$expenseCategoryId]['parent'] = [
					'name'=>$expenseCategoryId ,
					// 'cashExpenseCateCashExpenseCategory'=>$cashExpenseCategory,
				];
				foreach(ExpenseName::where('company_id',$company->id)->where('expense_type',$expenseCategoryId)->get() as $expense){
					$items[$expenseCategoryId]['sub_items'][$expense->id]['name'] =$expense->getName() ;
					$items[$expenseCategoryId]['sub_items'][$expense->id]['id'] =$expense->id ;
				}
		}
        return view('non_banking_services.expense-structure.index', [
			'company'=>$company,
			'items'=>$items,
			'title'=>__('Expenses'),
			'tableTitle'=>__('Expenses')
		]);
	}
	public function create(Company $company , Request $request){
		
		return view('non_banking_services.expense-structure.form', array_merge($this->getViewVars($company),['inEditMode'=>false]));
	}
	protected function getViewVars(Company $company,$expenseType=null,$expenseNames=[]){
		return [
			'company'=>$company ,
			'expenseNames'=>$expenseNames ,
			'expenseType'=>$expenseType,
			'title'=>__('Expenses'),
			'storeRoute'=>$expenseType ? route('update.expense.names',['company'=>$company->id,'expenseType'=>$expenseType]) :route('store.expense.names',['company'=>$company->id]),
		];
	}
	public function store(Company $company , StoreExpenseNamesRequest $request)
	{
		$expenseType  =$request->get('expense_type');
		$expenseNames = $request->get('expenseNames');
		foreach($expenseNames as $expenseNameArr){
			$name = $expenseNameArr['name'];
			$isEmployeeExpense = Arr::first($expenseNameArr['is_employee_expense']??[])??0;
			$isBranchExpense = Arr::first($expenseNameArr['is_branch_expense']??[])??0;
			$expenseName = ExpenseName::where('company_id',$company->id)->where('expense_type',$expenseType)->where('name',$name)->first();
			if(!$expenseName){
				ExpenseName::create([
					'company_id'=>$company->id ,
					'name'=>$name ,
					'expense_type'=>$expenseType ,
					'is_employee_expense'=>$isEmployeeExpense,
					'is_branch_expense'=>$isBranchExpense
				]);
			}
		}
		
		return response()->json([
			'redirectTo'=>route('view.expense.names',['company'=>$company->id])
		]);
	}
	public function getCommonData(Request $request,Company $company,string $expenseType = null,$cashExpenses = [])
	{
		return [
			'name'=>$request->get('name'),
			'expense_type'=>$expenseType,
			'company_id'=>$company->id ,
		];
	}
	public function edit(Request $request , Company $company , string $expenseType ){
		$expenseNames = ExpenseName::where('expense_type',$expenseType)->where('company_id',$company->id)->get();
		return view('non_banking_services.expense-structure.form', array_merge(['inEditMode'=>true],$this->getViewVars($company,$expenseType,$expenseNames)));
	}
	public function update(Request $request , Company $company , string $expenseType){
			$oldIdsFromDatabase = $company->expenseNamesFor($expenseType,$company->id)->pluck('id')->toArray();
		$additionalData = [
			'expense_type'=>$request->get('expense_type'),
		];
		
	
		$company->storeRepeaterRelations($request,['expenseNames'],$company,$additionalData,$oldIdsFromDatabase);
		
		return response()->json([
			'redirectTo'=>route('view.expense.names',['company'=>$company->id])
		]);
	}
	public function destroy(Request $request,Company  $company ,  $expenseType  ){
		$isExist = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('expenses')->where('company_id',$company->id)->where('expense_category',$expenseType)->count();
		if($isExist){
			return redirect()->back()->with('fail',__('This Item Cannot Be Deleted Because It’s Currently Used In A Study'));	
		}
		ExpenseName::where('company_id',$company->id)->where('expense_type',$expenseType)->delete();
		return redirect()->back()->with('success',__('Done !'));	
	}
	

}
