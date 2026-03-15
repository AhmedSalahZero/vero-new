<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HArr;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Department;
use App\Models\NonBankingService\Manpower;
use App\Models\NonBankingService\Position;
use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;

class ManpowerExpensesController extends Controller
{
	public function create(Company $company , Request $request,Study $study){
		return view('non_banking_services.manpower.form', $this->getViewVars($company,$study));
	}
	public function getOldData(Company $company , Request $request , Study $study)
	{
		$dates =array_map(function($date){
			return formatDateForView($date);
		},array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) );
		$type = 'manpower';
		// $expenseType = $type;
		$departments = $company->generalDepartments->load('positions.manpowers');
		
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths); 
		$manpowerType = 'general';
		$branchId = null ;
		$manpowers = $study->getManpowerFormattedForType($departments,$dates,$manpowerType,$branchId);
		$departmentWithPositions = $departments->map(function(Department $department){
			return [
				'id'=>$department->id,
				'name'=>$department->name,
				'expense_name'=>$department->getExpenseTypeName(),
				'positions'=>$department->positions->map(function(Position $position) use ($department){
						return [
							'id'=>$position->id,
							'department_id'=>$department->id,
							'name'=>$position->name,
							'expense_type'=>$position->getExpenseTypeId(),
						];
				})->toArray()
			];
		})->toArray()	;
		
		return [
			'submitUrl'=>route('store.department.positions.for.non.banking',['company'=>$company->id,'study'=>$study->id]),
			'dates'=>(object)$dates,
			'departmentWithPositions'=>$departmentWithPositions,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			'model'=>[
				'manpowers'=>$manpowers,
				],
		
		];
	}
	
	protected function getViewVars(Company $company, Study $study){
		
		
		return [
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
			'title'=>__('Manpower Expenses')  ,

		];
	}
	
	
	public function storeDepartmentPositions(Company $company , Request $request,Study $study){
		$study->saveManpowerForm($request);
		if($request->get('submit_button') == 'save'){
			return response()->json([
				'redirectTo'=>route('view.manpower.for.non.banking',['company'=>$company->id,'study'=>$study->id])
			]);
		}
		return response()->json([
			'redirectTo'=>route('non.banking.create.expenses',['company'=>$company->id,'study'=>$study->id])
		]);
		
	}
	public function getPositionsBasedOnDepartment(Company $company,Request $request,Study $study){
		$positions = [];
		foreach($request->get('departmentId',[]) as $departmentId){
			$department  = Department::find($departmentId);
			$currentPositions = $department->positions->pluck('name','id')->toArray();
			$positions = HArr::mergeTwoAssocArr($positions , $currentPositions);
		}
		return response()->json([
			'status'=>true ,
			'positions'=>$positions
		]);
	}
}
