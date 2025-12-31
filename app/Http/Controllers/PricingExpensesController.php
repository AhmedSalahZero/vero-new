<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PricingExpense;
	use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PricingExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Company  $company )
    {
		$expenses = DB::table('pricing_expenses')->where('company_id',$company->id)->get();
		$items = [];
		foreach($expenses as $index=>$expenseArr){
				$expenseType = camelizeWithSpace($expenseArr->expense_type,'-') ;
				$items[$expenseType][$index]['name'] =$expenseArr->name ;
				$items[$expenseType][$index]['id'] =$expenseArr->id ;
		}
        return view('admin.expenses.index',compact('company','expenses','items'));
    }

    public function create(Company $company)
    {
		return view('admin.expenses.crud',[
			'company'=>$company,
			'title'=>__('Create Expense'),
			'storeRoute'=>route('pricing-expenses.store',['company'=>$company->id ]),
			'viewAllRoute'=>route('pricing-expenses.index',['company'=>$company->id ]),
			'updateRoute'=>null ,
			'model'=>null,
			'expenseTypes'=>PricingExpense::getTypes()
		]);
		
    }

    public function store(Request $request , Company $company)
    {
		foreach($request->get('expenses',[]) as $expenseArr){
			$expenseType = $expenseArr['expense_type'] ;
			$expenseName = $expenseArr['name'] ; 
			$isExist = PricingExpense::where('company_id',$company->id)->where('expense_type',$expenseType)->where('name',$expenseName )->exists();
			if(!$isExist){
				PricingExpense::create([
					'expense_type'=>$expenseType ,
					'name'=>$expenseName ,
					'company_id'=>$company->id , 
					'created_by'=>auth()->user()->id ,
				]);
			}
		}
	
        Session::flash('success',__('Created Successfully'));
        return redirect()->route('pricing-expenses.index',['company'=>$company->id ]);

      
    }

    public function show($id)
    {
    }

    public function edit(Company $company,PricingExpense $pricingExpense  )
    {
		
		return view('admin.expenses.crud',[
			'company'=>$company ,
			'title'=>__('Edit Expense'),
			'storeRoute'=>route('pricing-expenses.store',['company'=>$company->id ]),
			'viewAllRoute'=>route('pricing-expenses.index',['company'=>$company->id]),
			'updateRoute'=>route('pricing-expenses.update',['pricing_expense'=>$pricingExpense->id,'company'=>$company->id ]) ,
			'model'=>$pricingExpense,
		'expenseTypes'=>PricingExpense::getTypes()
			
		]);
    }

   
    public function update(Request $request, Company $company , PricingExpense $pricingExpense)
    {
	
	
		$pricingExpense->update([
			'name'=>$request->get('name'),
			'expense_type'=>$request->get('expense_type'),
			'updated_by'=>auth()->user()->id 
		]);
				
				session::flash('success',__('Updated Successfully'));
				return redirect()->route('pricing-expenses.index',['company'=>$company->id ] );
    }

  
    public function destroy(Company $company , PricingExpense $pricingExpense)
    {
		try{
			$pricingExpense->delete();
		}
		catch(\Exception $e){
			
			return redirect()->back()->with('fail',__('This Expense Can Not Be Deleted , It Related To Another Record'));
		}
		
      

        return redirect()->back()->with('fail',__('Deleted Successfully'));

    }


    
}
