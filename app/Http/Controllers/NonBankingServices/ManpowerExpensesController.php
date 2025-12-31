<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HArr;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Department;
use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;

class ManpowerExpensesController extends Controller
{
	public function create(Company $company , Request $request,Study $study){
		return view('non_banking_services.manpower.form', $this->getViewVars($company,$study));
	}
	protected function getViewVars(Company $company, Study $study){
		$studyMonthsForViews =array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) ;
		return [
			'company'=>$company ,
			'type'=>getLastSegmentInRequest(),
			'study'=>$study,
			'model'=>$study ,
			'title'=>__('Manpower Expenses')  ,
			'expenseType'=>'manpower',
			'storeRoute'=>route('store.manpower.for.non.banking',['company'=>$company->id , 'study'=>$study->id]),
			'studyMonthsForViews'=>$studyMonthsForViews,
			'departments'=>$company->generalDepartments,
			'storeDepartmentPositionsRoute'=>route('store.department.positions.for.non.banking',['company'=>$company->id,'study'=>$study->id]),
			'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
			'manpowerType'=>'general'
		];
	}
	
	
	public function storeDepartmentPositions(Company $company , Request $request,Study $study){
		$study->saveManpowerForm($request);
		
		return response()->json([
			'redirectTo'=>route('create.expense.per.employees',['company'=>$company->id,'study'=>$study->id])
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
