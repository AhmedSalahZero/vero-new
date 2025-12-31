<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class MicrofinanceProductsController extends Controller
{
	use NonBankingService ;
	
	public function create(Company $company , Request $request){
		
		return view('non_banking_services.microfinance-products.form', $this->getViewVars($company));
	}
	protected function getViewVars(Company $company){
		
		return [
			'company'=>$company ,
			'model'=>$company ,
			// 'products'=>$company->getActiveMicrofinanceProducts(),
			'title'=>__('Microfinance Products'),
			'storeRoute'=>route('store.microfinance.products',['company'=>$company->id]),
		];
	}
	// public function edit(Company $company , Request $request,LeasingCategory $leasingCategory){
		
	// 	return view('non_banking_services.microfinance-products.form', $this->getViewVars($company));
	// }
	public function store(Company $company , Request $request)
	{
		$company->storeRepeaterRelations($request,['microfinanceProducts'],$company);
		return response()->json([
			'redirectTo'=>route('view.study',['company'=>$company->id])
			// 'redirectTo'=>route('create.leasing.categories',['company'=>$company->id])
		]);
	}
	// public function destroy(Request $request,Company $company , LeasingCategory $leasingCategory){
	// 	$leasingCategory->delete();
	// 	return response()->json([
	// 		'redirectTo'=>route('view.leasing.categories',['company'=>$company->id])
	// 	]);
	// }
}
