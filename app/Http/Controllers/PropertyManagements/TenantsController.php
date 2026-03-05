<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyManagements\StoreExpenseNamesRequest;
use App\Models\Company;
use App\Models\PropertyManagement\Tenant;
use App\Traits\PropertyManagement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TenantsController extends Controller
{
	use PropertyManagement ;
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
		->when($request->has('value'),function($collection) use ($value,$searchFieldName){
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
		
		$tenants = $company->tenants;
        return view('property_managements.tenants.index', [
			'company'=>$company,
			'tenants'=>$tenants,
			'title'=>__('Tenants'),
			'tableTitle'=>__('Tenants')
		]);
	}
	public function create(Company $company , Request $request){
		
		return view('property_managements.tenants.form', [
			'company'=>$company,
			'title'=>__('Tenants'),
			'storeRoute'=>route('property.management.store.tenants',['company'=>$company->id]),
			'inEditMode'=>false,
			'tenants'=>[]
		]);
	}
	protected function getViewVars(Company $company,$tenants=[]){
	
		return [
			'company'=>$company ,
			'tenants'=>$tenants ,
			'title'=>__('Tenants'),
			'storeRoute'=>route('property.management.store.tenants',['company'=>$company->id]),
		];
	}
	public function store(Company $company , StoreExpenseNamesRequest $request)
	{
		$tenants = $request->get('tenants');
		foreach($tenants as $tenantArr){
			$name = $tenantArr['name'];
			$tenantArr['company_id'] = $company->id;
			$tenant = Tenant::where('company_id',$company->id)->where('name',$name)->first();
			if(!$tenant){
				Tenant::create($tenantArr);
			}
		}
	
		return response()->json([
			'redirectTo'=>route('property.management.view.tenants',['company'=>$company->id])
		]);
	}
	public function getCommonData(Request $request,Company $company)
	{
		return [
			'name'=>$request->get('name'),
			'nature'=>$request->get('nature'),
			'business_sector'=>$request->get('business_sector'),
			'related_party'=>$request->get('related_party'),
			'company_id'=>$company->id ,
		];
	}
	public function edit(Request $request , Company $company , Tenant $tenant ){
		return view('property_managements.tenants.form', [
			'company'=>$company,
			'title'=>__('Tenants'),
			'storeRoute'=>route('property.management.update.tenants',['company'=>$company->id,'tenant'=>$tenant->id]),
			'inEditMode'=>true,
			'tenants'=>[$tenant]
		]);
	}
	public function update(Request $request , Company $company , Tenant $tenant ){
		$data = $request->input('tenants.0');
		$tenant->update($data);
		return response()->json([
			'redirectTo'=>route('property.management.view.tenants',['company'=>$company->id])
		]);
	}
	public function destroy(Request $request,Company  $company , Tenant $tenant  ){
		$tenant->delete();
		return redirect()->back()->with('success',__('Done !'));
	
	}
	

}
