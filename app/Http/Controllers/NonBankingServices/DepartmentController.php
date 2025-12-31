<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreDepartmentsRequest;
use App\Models\Company;
use App\Models\NonBankingService\Department;
use App\Models\NonBankingService\Position;
use App\Traits\NonBankingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
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
		$company->syncMicrofinanceDepartments();
		$numberOfMonthsBetweenEndDateAndStartDate = 18 ;
		$currentType = $request->get('active',Department::GENERAL);
		$microfinanceDepartment = Department::where('company_id',$company->id)->where('type',Department::MICROFINANCE)->first();
		$filterDates = [];
		foreach([Department::GENERAL , Department::MICROFINANCE] as $type){
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
		
		$startDate = $filterDates[Department::GENERAL]['startDate'] ?? null ;
		$endDate = $filterDates[Department::GENERAL]['endDate'] ?? null ;
		$generalDepartments = $company->generalDepartments ;
		$microfinanceDepartments = $company->microfinanceDepartments ;
		// $departments =  $departments->filterByDateColumn('study_start_date',$startDate,$endDate) ;
		$departments =  $currentType == Department::GENERAL ? $this->applyFilter($request,$generalDepartments):$generalDepartments ;
		$microfinanceDepartments =  $currentType == Department::MICROFINANCE ? $this->applyFilter($request,$microfinanceDepartments):$microfinanceDepartments ;

		/**
		 * * end of bank to safe internal money transfer 
		 */
		 
		
		 $searchFields = [
			Department::GENERAL=>[
				'name'=>__('Name'),
			],
		];
	
		$models = [
			Department::GENERAL =>$departments ,
			Department::MICROFINANCE =>$microfinanceDepartments ,
		];

        return view('non_banking_services.departments.index', [
			'company'=>$company,
			'searchFields'=>$searchFields,
			'models'=>$models,
			'filterDates'=>$filterDates,
			'title'=>__('Departments'),
			'tableTitle'=>__('Departments'),
			'microfinanceDepartment'=>$microfinanceDepartment
		]);
		
		
		
	}
	public function create(Company $company , Request $request,string $type){
		
		return view('non_banking_services.departments.form', $this->getViewVars($company,$type,null));
	}
	protected function getViewVars(Company $company,string $type,$model = null){
		
		
		return [
			'company'=>$company ,
			'department'=>$model ,
	
			'type'=>$type,
			'title'=>__('Departments'),
			'storeRoute'=>isset($model) ? route('update.departments',['company'=>$company->id,'department'=>$model->id,'type'=>$type]) :route('store.departments',['company'=>$company->id,'type'=>$type]),
		];
	}
	public function store(Company $company , StoreDepartmentsRequest $request,string $type)
	{
		$department = Department::create($this->getCommonData($request,$company,$type));
		$expenseType = $request->get('expense_type');
		$department->storeRepeaterRelations($request,['positions'],$company,['expense_type'=>$expenseType]);
		return response()->json([
			'redirectTo'=>route('view.departments',['company'=>$company->id])
		]);
	}
	public function getCommonData(Request $request,Company $company,string $type)
	{
		return [
			'name'=>$request->get('name'),
			// 'expense_type'=>$request->get('expense_type'),
			'type'=>$type,
			'company_id'=>$company->id 
		];
	}
	public function edit(Request $request , Company $company , Department $department,string $type){
		if($type == Department::MICROFINANCE){
			return view('non_banking_services.microfinance-departments.form', $this->getViewVars($company,$type,$department));
		}
		return view('non_banking_services.departments.form', $this->getViewVars($company,$type,$department));
	}
	public function update(Request $request , Company $company , Department $department,string $type){
		$department->update($this->getCommonData($request,$company,$type));
		$additionalData = $type == Department::GENERAL ? [
			'expense_type'=>$request->get('expense_type')
		] : [];
		$department->storeRepeaterRelations($request,['positions'],$company,$additionalData);
		
		return response()->json([
			'redirectTo'=>route('view.departments',['company'=>$company->id])
		]);
	}
	public function destroy(Request $request,Company  $company , Department $department  ){
		$canBeDeleted = true ;
		$department->positions->each(function(Position $position) use ($company,&$canBeDeleted){
			$isExist = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('manpowers')->where('company_id',$company->id)->where('position_id',$position->id)->count();
			if($isExist){
				$canBeDeleted = false ;
			}
			
		}) ;
		if($canBeDeleted){
			$department->delete();
			return redirect()->back()->with('success',__('Done !'));	
			
		}
		return redirect()->back()->with('fail',__('This Item Cannot Be Deleted Because It’s Currently Used In A Study'));	
	}

}
