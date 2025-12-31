<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudyRequest;
use App\Models\Company;
use App\Models\NonBankingService\Consolidation;
use App\Models\NonBankingService\Study;
use Artisan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudyController extends Controller
{
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
		->sortByDesc('id')->values();
		
		return $collection;
	}
	
    public function index(Company $company , Request $request){
		
		$numberOfMonthsBetweenEndDateAndStartDate = 18 ;
		$currentType = $request->get('active',Study::STUDY);
		
		$filterDates = [];
		foreach([Study::STUDY] as $type){
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
		
		$startDate = $filterDates[Study::STUDY]['startDate'] ?? null ;
		$endDate = $filterDates[Study::STUDY]['endDate'] ?? null ;
		$studies = $company->studies ;
	//	$studies =  $studies->filterByDateColumn('study_start_date',$startDate,$endDate) ;
		$studies =  $currentType == Study::STUDY ? $this->applyFilter($request,$studies):$studies ;
		
		$monthlyStudies = $studies->filter(function($study){
			return $study->duration_in_years > 1;
			// return !$study->isMonthlyStudy();
		}); 
		$yearlyStudies = $studies->filter(function($study){
			return $study->duration_in_years <= 1;
		}); 
		
		// $monthlyStudies = $studies->filter(function($study){
		// 	return !$study->isMonthlyStudy();
		// 	// return !$study->isMonthlyStudy();
		// }); 
		// $yearlyStudies = $studies->filter(function($study){
		// 	return $study->isMonthlyStudy();
		// }); 
		/**
		 * * end of bank to safe internal money transfer 
		 */
		 
		
		 $searchFields = [
			Study::STUDY=>[
				'name'=>__('Name'),
				'study_start_date'=>__('Study Start Date'),
				'study_end_date'=>__('Study End Date'),
			],
		];
		$consolidations = Consolidation::where('company_id',$company->id)->get();
		$models = [
			Study::BUSINESS_PLAN =>$monthlyStudies ,
			Study::ANNUALLY_STUDY =>$yearlyStudies ,
			Study::CONSOLIDATION=>$consolidations
		];
        return view('non_banking_services.study.index', [
			'company'=>$company,
			'searchFields'=>$searchFields,
			'models'=>$models,
			'filterDates'=>$filterDates,
			'title'=>__('Studies'),
			'tableTitle'=>__('Studies')
		]);
		
		
		
	}
	protected function getViewVars(Company $company, Study $model = null , $isBusinessPlan = true ):array 
	{
		$isBusinessPlan = $model ? $model->isBusinessPlan() : $isBusinessPlan;
		$formattedExistingBranches = $company->getExistingBranchesFormattedForSelect();

		return [
			'company'=>$company,
			'formattedExistingBranches'=>$formattedExistingBranches,
			'title'=>$company->getName().' ' . __(' Financial Plan'),
			'model'=>$model,
			'storeRoute'=>route('store.non.banking.services',['company'=>$company->id]),
			'navigators' => [],
			'isBusinessPlan'=>$isBusinessPlan
		];
	}
	public function create(Company $company , Request $request){
		$isBusinessPlan  = $request->get('is_business_plan') == 1; 
		return view('non_banking_services.study.form', $this->getViewVars($company,null,$isBusinessPlan));
	}
	public function store(Company $company , StoreStudyRequest $request , Study $study = null)
	{
		$studyStartDate = $request->get('study_start_date').'-01';
		$operationStartDate = $request->get('operation_start_date') . '-01';
		$request->merge([
			'study_start_date'=>Carbon::make($studyStartDate)->format('Y-m-d'),
			'study_end_date'=>Carbon::make($request->get('study_end_date'))->format('Y-m-d'),
			'operation_start_date'=>Carbon::make($operationStartDate)->format('Y-m-d'),
			'has_leasing'=>$request->boolean('has_leasing'),
			'has_direct_factoring'=>$request->boolean('has_direct_factoring'),
			'has_reverse_factoring'=>$request->boolean('has_reverse_factoring'),
			'has_ijara_mortgage'=>$request->boolean('has_ijara_mortgage'),
			'has_portfolio_mortgage'=>$request->boolean('has_portfolio_mortgage'),
			'has_micro_finance'=>$request->boolean('has_micro_finance'),
			'has_securitization'=>$request->boolean('has_securitization'),
			'has_consumer_finance'=>$request->boolean('has_consumer_finance'),
			
		]);
		$data = $request->except(['_token']) ;
		$model = null ;
		if(is_null($study)){
			$model = Study::create($data);
		}else{
			$study->update($data);
			$model = $study;
		}
		$incomeStatementExist=DB::connection('non_banking_service')->table('income_statement_reports')->where('study_id',$model->id)->first();
		if(!$incomeStatementExist){
			DB::connection('non_banking_service')->table('income_statement_reports')->insert([
				'study_id'=>$model->id,
				'company_id'=>$model->company->id,
			]);
			
			DB::connection('non_banking_service')->table('cashflow_statement_reports')->insert([
				'study_id'=>$model->id,
				'company_id'=>$model->company->id,
			]);
			
		}
		/**
		 * @var Study $model
		 */
		$datesAsStringAndIndex = $model->getDatesAsStringAndIndex();
		$studyDates = $model->getStudyDates() ;
		$datesAndIndexesHelpers = $model->datesAndIndexesHelpers($studyDates);
		$datesIndexWithYearIndex=$datesAndIndexesHelpers['datesIndexWithYearIndex']; 
		$yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear']; 
		$dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate']; 
		$dateWithMonthNumber=$datesAndIndexesHelpers['dateWithMonthNumber']; 
		$model->updateStudyAndOperationDates($datesAsStringAndIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate,$dateWithMonthNumber);
		return response()->json([
			'redirectTo'=>route('create.general.assumption',['company'=>$company->id,'study'=>$model->id])
		]);
	}
	public function edit(Company $company , Request $request,Study $study){
		return view('non_banking_services.study.form', $this->getViewVars($company,$study));
	}
	public function update(StoreStudyRequest $request , Company $company,Study $study)
	{
		return $this->store($company,$request,$study);
	}
	public function destroy(Request $request , Company $company,Study $study)
	{
		Artisan::call('delete:study',['study_id'=>$study->id]);
		$active = $study->getActiveTab();
		$study->delete();
		return redirect()->route('view.study',['company'=>$company->id,'active'=>$active]);
		
	}
}
