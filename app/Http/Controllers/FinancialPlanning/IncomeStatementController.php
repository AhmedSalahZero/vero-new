<?php

namespace App\Http\Controllers\FinancialPlanning;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FinancialPlanning\Study;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeStatementController extends Controller
{
    public function index(Company $company , Request $request,Study $study){
		
		$studyMonthsForViews=$study->getStudyDurationPerYearFromIndexesForView();
		$sumQuery = '';
		foreach($studyMonthsForViews as 	$dateAsIndex => $dateAsString){
				$sumQuery .= 'SUM(JSON_UNQUOTE(JSON_EXTRACT(salary_expenses, "$['.$dateAsIndex.']"))) as salary_expenses_'.$dateAsIndex.',';
		}
		$sumQuery = rtrim($sumQuery,',');
		$salaryExpensePerTypeAndExpenseTypes = DB::connection(FINANCIAL_PLANNING_CONNECTION_NAME)
		->table('departments')
		->join('positions', 'positions.department_id', '=', 'departments.id')
		->where('departments.study_id', $study->id)
		->selectRaw('
			type, 
			expense_type, 
			SUM(JSON_LENGTH(salary_expenses)) as months_count,
		'.$sumQuery)
		->groupBy('type', 'expense_type')
		->get()->keyBy(function($item){
			return $item->type .'$$$$'.$item->expense_type;
		})->toArray();

        return view('financial_planning.income-statement.forecast', [
			'company'=>$company,
			'studyMonthsForViews'=>$studyMonthsForViews,
			'title'=>__('Forecasted Income Statement'),
			'tableTitle'=>__('Forecasted Income Statement'),
			'createRoute'=>route('create.financial.planning.study',['company'=>$company->id]),
			'studyMonths'=>$study->getStudyDurationPerYearFromIndexes(),
			'study'=>$study,
			'salaryExpensePerTypeAndExpenseTypes'=>$salaryExpensePerTypeAndExpenseTypes,
			'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber()
		]);
		
		
		
	}
	protected function getViewVars(Company $company , Study $model = null):array 
	{
		$actionRoute=isset($model) ? route('update.study',[$company->id , $model->id]) : route('store.financial.planning.study',['company'=>$company->id]);
		return [
			'company'=>$company,
			'title'=>$company->getName().' ' . __(' Financial Plan'),
			'model'=>$model,
			'actionRoute'=>$actionRoute,
			'navigators' => [],
		];
	}
	public function create(Company $company , Request $request){
		return view('financial_planning.study.form', $this->getViewVars($company));
	}
	public function store(Company $company , Request $request , Study $study = null)
	{
		$request->merge([
			'study_start_date'=>Carbon::make($request->get('study_start_date'))->format('Y-m-d'),
			'study_end_date'=>Carbon::make($request->get('study_end_date'))->format('Y-m-d'),
			'operation_start_date'=>Carbon::make($request->get('operation_start_date'))->format('Y-m-d'),
	
			
		]);
		$data = $request->except(['_token']) ;
		$model = null ;
		if(is_null($study)){
			$model = Study::create($data);
		}else{
			$study->update($data);
			$model = $study;
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
