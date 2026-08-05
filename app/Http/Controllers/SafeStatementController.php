<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SafeStatementController
{
    use GeneralFunctions;
    public function index(Company $company)
	{
		$selectedBranches =  Branch::getBranchesForCurrentCompany($company->id) ;
        return view('safe_statement_form', [
			'company'=>$company,
			'selectedBranches'=>$selectedBranches
		]);
    }
	public function result(Company $company , Request $request){
		
		$startDate = $request->get('start_date');
		$endDate = $request->get('end_date');
		$branchId = $request->get('branch_id');
		$currency = $request->get('currency');
		$results=DB::table('cash_in_safe_statements')
		->where('company_id',$company->id)
		->where('currency',$currency)
		->where('branch_id',$branchId)
		->where('date','>=',$startDate)
		->where('date','<=',$endDate)
		/**
		 * * الترتيب بـ full_date مش date: الرصيد الجاري
		 * * (beginning_balance/end_balance) بيتسلسل في CashInSafeStatement
		 * * بـ 'full_date asc , id asc'، فالترتيب بالتاريخ من غير وقت كان
		 * * بيقلب الصفوف اللي في نفس اليوم ويخلي رصيد آخر صف مش متصل
		 * * برصيد اللي بعده
		 */
		->orderByRaw('full_date desc , id desc')
		->get();
			if(!count($results)){
				return redirect()
									->back()
									->with('fail',__('No Data Found'))	
									;
			}
		return view('safe_statement_result',[
			'results'=>$results,
			'currency'=>$currency
		]);
	}




}
