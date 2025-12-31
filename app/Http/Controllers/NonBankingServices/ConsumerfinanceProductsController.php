<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class ConsumerfinanceProductsController extends Controller
{
	use NonBankingService ;
	
	// protected function applyFilter(Request $request,Collection $collection):Collection{
	// 	if(!count($collection)){
	// 		return $collection;
	// 	}
	// 	$searchFieldName = $request->get('field');
	// 	$dateFieldName =  'created_at' ; // change it 
	// 	// $dateFieldName = $searchFieldName === 'balance_date' ? 'balance_date' : 'created_at'; 
	// 	$from = $request->get('from');
	// 	$to = $request->get('to');
	// 	$value = $request->query('value');
	// 	$collection = $collection
	// 	->when($request->has('value'),function($collection) use ($request,$value,$searchFieldName){
	// 		return $collection->filter(function($moneyReceived) use ($value,$searchFieldName){
	// 			$currentValue = $moneyReceived->{$searchFieldName} ;
			
	// 			return false !== stristr($currentValue , $value);
	// 		});
	// 	})
	// 	->when($request->get('from') , function($collection) use($dateFieldName,$from){
	// 		return $collection->where($dateFieldName,'>=',$from);
	// 	})
	// 	->when($request->get('to') , function($collection) use($dateFieldName,$to){
	// 		return $collection->where($dateFieldName,'<=',$to);
	// 	})
	// 	->sortByDesc('id')->values();
		
	// 	return $collection;
	// }
	
    // public function index(Company $company , Request $request){
		
	// 	$numberOfMonthsBetweenEndDateAndStartDate = 18 ;
	// 	$currentType = $request->get('active',Study::LEASING_CATEGORY);
		
	// 	$filterDates = [];
	// 	foreach([Study::LEASING_CATEGORY] as $type){
	// 		$startDate = $request->has('startDate') ? $request->input('startDate.'.$type) : now()->subMonths($numberOfMonthsBetweenEndDateAndStartDate)->format('Y-m-d');
	// 		$endDate = $request->has('endDate') ? $request->input('endDate.'.$type) : now()->format('Y-m-d');
			
	// 		$filterDates[$type] = [
	// 			'startDate'=>$startDate,
	// 			'endDate'=>$endDate
	// 		];
	// 	}
		
		
		 
	// 	  /**
	// 	 * * start of bank to safe internal money transfer 
	// 	 */
		
	// 	$startDate = $filterDates[Study::STUDY]['startDate'] ?? null ;
	// 	$endDate = $filterDates[Study::STUDY]['endDate'] ?? null ;
	// 	$models = $company->leasingCategories ;
	// 	$models =  $models->filterByDateColumn('created_at',$startDate,$endDate) ;
	// 	$models =  $currentType == Study::STUDY ? $this->applyFilter($request,$models):$models ;

	// 	/**
	// 	 * * end of bank to safe internal money transfer 
	// 	 */
		 
		
	// 	 $searchFields = [
	// 		Study::LEASING_CATEGORY=>[
	// 			'title'=>__('Name'),
	// 		],
	// 	];
	// 	$models = [
	// 		Study::LEASING_CATEGORY =>$models ,
	// 	];

    //     return view('non_banking_services.consumerfinance-products.index', [
	// 		'company'=>$company,
	// 		'searchFields'=>$searchFields,
	// 		'models'=>$models,
	// 		'filterDates'=>$filterDates,
	// 		'title'=>__('Leasing Products'),
	// 		'tableTitle'=>__('Leasing Products')
	// 	]);
		
		
		
	// }
	
	public function create(Company $company , Request $request){
		
		return view('non_banking_services.consumerfinance-products.form', $this->getViewVars($company));
	}
	protected function getViewVars(Company $company){
		return [
			'company'=>$company ,
			'model'=>$company ,
			'title'=>__('Consumerfinance Products'),
			'storeRoute'=>route('store.consumerfinance.products',['company'=>$company->id]),
		];
	}
	// public function edit(Company $company , Request $request,LeasingCategory $leasingCategory){
		
	// 	return view('non_banking_services.consumerfinance-products.form', $this->getViewVars($company));
	// }
	public function store(Company $company , Request $request)
	{
		$company->storeRepeaterRelations($request,['consumerfinanceProducts'],$company);
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
