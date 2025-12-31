<?php

namespace App\Http\Controllers\FinancialPlanning;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FinancialPlanning\Study;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudyController extends Controller
{
	// protected function applyFilter(Request $request,Collection $collection):Collection{
	// 	if(!count($collection)){
	// 		return $collection;
	// 	}
	// 	$searchFieldName = $request->get('field');
	// 	$dateFieldName =  'created_at' ;
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
	
    public function index(Company $company , Request $request){
		
		// $numberOfMonthsBetweenEndDateAndStartDate = 18 ;
		// $currentType = $request->get('active',Study::STUDY);
		
		// $filterDates = [];
		// foreach([Study::STUDY] as $type){
		// 	$startDate = $request->has('startDate') ? $request->input('startDate.'.$type) : now()->subMonths($numberOfMonthsBetweenEndDateAndStartDate)->format('Y-m-d');
		// 	$endDate = $request->has('endDate') ? $request->input('endDate.'.$type) : now()->format('Y-m-d');
			
		// 	$filterDates[$type] = [
		// 		'startDate'=>$startDate,
		// 		'endDate'=>$endDate
		// 	];
		// }
		
		
		 
		  /**
		 * * start of bank to safe internal money transfer 
		 */
		
		// $startDate = $filterDates[Study::STUDY]['startDate'] ?? null ;
		// $endDate = $filterDates[Study::STUDY]['endDate'] ?? null ;
		$studies = $company->financialPlanningStudies ;
		// $studies =  $studies->filterByDateColumn('study_start_date',$startDate,$endDate) ;
		// $studies =   $this->applyFilter($request,$studies) ;
		// $studies =  $currentType == Study::STUDY ? $this->applyFilter($request,$studies):$studies ;

		/**
		 * * end of bank to safe internal money transfer 
		 */
		 
		
		//  $searchFields = [
		// 	Study::STUDY=>[
		// 		'name'=>__('Name'),
		// 		'study_start_date'=>__('Study Start Date'),
		// 		'study_end_date'=>__('Study End Date'),
		// 	],
		// ];
	
		$models = [
			Study::STUDY =>$studies ,
		];

        return view('financial_planning.study.index', [
			'company'=>$company,
			// 'searchFields'=>$searchFields,
			'models'=>$models,
			// 'filterDates'=>$filterDates,
			'title'=>__('Studies'),
			'tableTitle'=>__('Studies'),
			'createRoute'=>route('create.financial.planning.study',['company'=>$company->id])
		]);
		
		
		
	}
	protected function getViewVars(Company $company , Study $model = null):array 
	{
		$actionRoute=isset($model) ? route('update.financial.planning.study',[$company->id , $model->id]) : route('store.financial.planning.study',['company'=>$company->id]);
		$relations = Study::getRelationName();
		$firstNewItems = $model ? $model->{$relations[$model->getMainPlanningBase()]} : [-1];
		$secondNewItems = $model ? $model->{$relations[$model->getSubPlanningBase()]} : [-1];

		return [
			'company'=>$company,
			'title'=>$company->getName().' ' . __(' Financial Plan'),
			'model'=>$model,
			'actionRoute'=>$actionRoute,
			'mainPlanningBasesForSelector'=>$company->getMainPlanningBasesForSelector(),
			'navigators' => [],
			'firstNewItems'=>$firstNewItems,
			'secondNewItems'=>$secondNewItems,
		];
	}
	public function create(Company $company , Request $request){
		return view('financial_planning.study.form', $this->getViewVars($company));
	}
	public function store(Company $company , Request $request , Study $study = null)
	{
		$relations = Study::getRelationName();
		$mainRelationName = $request->boolean('add_new_from_main_planning')? $relations[$request->get('main_planning_base')]??null : null;
		$secondRelationName =$request->boolean('add_new_from_sub_planning')? $relations[$request->get('sub_planning_base')]??null : null;
		
		
		
		$request->merge([
			'study_start_date'=>Carbon::make($request->get('study_start_date'))->format('Y-m-d'),
			'study_end_date'=>Carbon::make($request->get('study_end_date'))->format('Y-m-d'),
			'operation_start_date'=>Carbon::make($request->get('operation_start_date'))->format('Y-m-d'),
			'has_trading'=>$request->boolean('has_trading'),
			'has_manufacturing'=>$request->boolean('has_manufacturing'),
			'has_service'=>$request->boolean('has_service'),
			'has_service_with_inventory'=>$request->boolean('has_service_with_inventory'),
			'add_new_from_main_planning'=>$request->boolean('add_new_from_main_planning'),
			'add_new_from_sub_planning'=>$request->boolean('add_new_from_sub_planning'),
		]);
		$data = $request->except(['_token']) ;
		$model = null ;
		if(is_null($study)){
			$model = Study::create($data);
		}else{
			$study->update($data);
			$model = $study;
		}
		foreach($relations as $relationName){
		
			$model->{$relationName}()->delete();
		}
		if($mainRelationName){
			foreach($request->get('first_new_items',[]) as $firstItemsArr){
				$model->{$mainRelationName}()->create([
					'name'=>$firstItemsArr['name'],
					'is_new'=>1 ,
					'is_existing'=> 0 ,
					'company_id'=>$company->id 
				]);
			}
		}

		if($secondRelationName){
			foreach($request->get('second_new_items',[]) as $secondItemsArr){
				$model->{$secondRelationName}()->create([
					'name'=>$secondItemsArr['name'],
					'is_new'=>1 ,
					'is_existing'=> 0 ,
					'company_id'=>$company->id 
				]);
			}
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
			'redirectTo'=>route('view.financial.planning.income.statement',['company'=>$company->id,'study'=>$model->id])
		]);
	}
	public function edit(Company $company , Request $request,Study $study){
		return view('financial_planning.study.form', $this->getViewVars($company,$study));
	}
	public function update(Request $request , Company $company,Study $study)
	{
		return $this->store($company,$request,$study);
	}
	public function destroy(Request $request , Company $company,Study $study)
	{
		$study->delete();
		return redirect()->back()->with('success',__('Study Has Been Deleted Successfully'));
	}
}
