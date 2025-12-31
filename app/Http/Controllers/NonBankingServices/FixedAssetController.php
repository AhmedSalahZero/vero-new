<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreFixedAssetNamesRequest;
use App\Models\Company;
use App\Models\NonBankingService\FixedAssetName;
use App\Traits\NonBankingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends Controller
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
		
		$numberOfMonthsBetweenEndDateAndStartDate = 18 ;
		$currentType = $request->get('active',FixedAssetName::FIXED_ASSET);
		
		$filterDates = [];
		foreach([FixedAssetName::FIXED_ASSET] as $type){
			$startDate = $request->has('startDate') ? $request->input('startDate.'.$type) : now()->subMonths($numberOfMonthsBetweenEndDateAndStartDate)->format('Y-m-d');
			$endDate = $request->has('endDate') ? $request->input('endDate.'.$type) : now()->format('Y-m-d');
			
			$filterDates[$type] = [
				'startDate'=>$startDate,
				'endDate'=>$endDate
			];
		}
		
		
		 
		  /**
		 * * start of bank to safe internal money transfer 
		 */
		
		$startDate = $filterDates[FixedAssetName::FIXED_ASSET]['startDate'] ?? null ;
		$endDate = $filterDates[FixedAssetName::FIXED_ASSET]['endDate'] ?? null ;
		$fixedAssetNames = $company->fixedAssetNames ;
		// $fixedAssetNames =  $fixedAssetNames->filterByDateColumn('study_start_date',$startDate,$endDate) ;
		$fixedAssetNames =  $currentType == FixedAssetName::FIXED_ASSET ? $this->applyFilter($request,$fixedAssetNames):$fixedAssetNames ;

		/**
		 * * end of bank to safe internal money transfer 
		 */
		 
		
		 $searchFields = [
			FixedAssetName::FIXED_ASSET=>[
				'name'=>__('Name'),
			],
		];
	
		$models = [
			FixedAssetName::FIXED_ASSET =>$fixedAssetNames ,
		];

        return view('non_banking_services.fixed-asset-structure.index', [
			'company'=>$company,
			'searchFields'=>$searchFields,
			'models'=>$models,
			'filterDates'=>$filterDates,
			'title'=>__('Fixed Asset Names'),
			'tableTitle'=>__('Fixed Asset Names')
		]);
		
		
		
	}
	
   
	public function create(Company $company , Request $request){
		
		return view('non_banking_services.fixed-asset-structure.form', array_merge($this->getViewVars($company),['inEditMode'=>false]));
	}
	protected function getViewVars(Company $company,?FixedAssetName $fixedAssetName = null){
		return [
			'company'=>$company ,
			'title'=>__('Fixed Asset Names'),
			'fixedAssetNames'=>$fixedAssetName ? $company->fixedAssetNames : [],
			'model'=>$fixedAssetName,
			'storeRoute'=>$fixedAssetName ? route('update.fixed.asset.names',['company'=>$company->id,'fixedAssetName'=>$fixedAssetName->id]) : route('store.fixed.asset.names',['company'=>$company->id]),
		];
	}
	public function store(Company $company , StoreFixedAssetNamesRequest $request)
	{
		foreach($request->get('fixedAssetNames') as $fixedAsset){
			$name = $fixedAsset['name'];
			$isEmployeeAsset = $fixedAsset['is_employee_asset'][0]??0 ;
			$isBranchAsset = $fixedAsset['is_branch_asset'][0]??0 ;
			DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_asset_names')->insert([
				'company_id'=>$company->id,
				'name'=>$name ,
				'is_employee_asset'=>$isEmployeeAsset,
				'is_branch_asset'=>$isBranchAsset,
			]);
		}
		// $company->storeRepeaterRelations($request,['fixedAssetNames'],$company);
		return response()->json([
			'redirectTo'=>route('view.fixed.asset.names',['company'=>$company->id])
		]);
	}
	public function getCommonData(Request $request,Company $company)
	{
		return [
			'name'=>$request->get('name'),
			'company_id'=>$company->id ,
		];
	}
	
	
	public function edit(StoreFixedAssetNamesRequest $request , Company $company , FixedAssetName $fixedAssetName ){
		$fixedAssetNames = $company->fixedAssetNames->where('id',$fixedAssetName->id);
		return view('non_banking_services.fixed-asset-structure.form', array_merge($this->getViewVars($company,$fixedAssetName),['inEditMode'=>true,'fixedAssetNames'=>$fixedAssetNames]));
	}
	public function update(Request $request , Company $company , FixedAssetName $fixedAssetName){
		$name = $request->input('fixedAssetNames.0.name');
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_asset_names')->where('id',$fixedAssetName->id)->update([
			'name'=>$name ,
			'is_branch_asset'=>$request->boolean('is_branch_asset'),
			'is_employee_asset'=>$request->boolean('is_employee_asset')
			
		]);
		// $fixedAssetName->update([
		// ]);
		// $company->update($this->getCommonData($request,$company,$request->get('expense_type')));
		// 	$oldIdsFromDatabase = $company->expenseNamesFor($expenseType,$company->id)->pluck('id')->toArray();
		// $additionalData = [
		// 	'expense_type'=>$request->get('expense_type'),
		// ];
	
		// $company->storeRepeaterRelations($request,['expenseNames'],$company,$additionalData,$oldIdsFromDatabase);
		
		return response()->json([
			'redirectTo'=>route('view.fixed.asset.names',['company'=>$company->id])
		]);
	}
	public function destroy(Request $request,Company  $company ,  FixedAssetName $fixedAssetName  ){
		$isExist = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_assets')->where('company_id',$company->id)->where('name_id',$fixedAssetName->id)->count();
		if($isExist){
			return redirect()->back()->with('fail',__('This Item Cannot Be Deleted Because It’s Currently Used In A Study'));	
		}
		$fixedAssetName->delete();
		return redirect()->back()->with('success',__('Done !'));	
	}
	
	

}
