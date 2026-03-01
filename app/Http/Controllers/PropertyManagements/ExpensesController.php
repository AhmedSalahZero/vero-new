<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Helpers\HHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpensesRequest;
use App\Models\Company;
use App\Models\PropertyManagement\Expense;
use App\Models\PropertyManagement\ExpenseName;
use App\Models\PropertyManagement\Position;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Arr;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    use PropertyManagement ;
    public function create(Company $company, Request $request, Study $study)
    {
        return view('property_managements.expenses.form', $this->getViewVars($company, $study));
    }
     public function expensesGetVueOldData(Company $company, Request $request, Study $study)
    {
		$increaseYearsFormatted = $study->getYearlyIndexes();
		unset($increaseYearsFormatted[0]);
		$increaseYearsFormatted = array_values(array_map(function($item){
			return explode('-',$item)[1];
		},$increaseYearsFormatted));
		$departmentsFormatted = $company->departments->sortBy('name')->pluck('name','id')->toArray() ;
		$departments = [];
		
		foreach($departmentsFormatted as $id=>$title){
			$departments[] = [
				'id'=>$id,
				'title'=>$title
			];
		}
		$positionPerDepartments = [];
		foreach($departments as $departmentArr){
			$departmentId = $departmentArr['id'] ;
			$positions = Position::where('department_id',$departmentId)->pluck('name','id')->toArray();
			foreach($positions as $positionId => $positionName){
				$positionPerDepartments[$departmentId][] = [
					'id'=>$positionId,
					'title'=>$positionName
				];
			}
		}

		
		$selectedRevenueStreams = $study->getRevenueStreamTypes();
		
		$revenueCategoriesPerRevenue = [];
		foreach($selectedRevenueStreams as $revenueStreamArr){
			$revenueId = $revenueStreamArr['id'];
			$categories = $revenueStreamArr['categories'];
			$revenueCategoriesPerRevenue[$revenueId] =$categories;
		}
		
        $expenseCategoriesFormatted = ExpenseName::getExpenseCategoriesFormatted();
		$expenseNamesPerCategories = ExpenseName::getExpenseNamesPerCategories();
		
		  
       
        $revenueStreamsPerBusinessUnits = [];
      foreach(getTypesForValuesForPropertyManagement() as $expenseType => $expenseArr){
			$isOneTimeExpense = $expenseType == 'one_time_expense'; 
		  $expenses = Expense::where('study_id', $study->id)->where('relation_name', $expenseType)->where('expense_type','Expense')->get();
			
		 foreach (count($expenses) ? $expenses : [null] as $expense) {
			/**
			 * @var ?Expense $expense
			 */
            if ($expense) {
                $revenueStreamsPerBusinessUnits[$expense->id] = [];
            }
            $expensesPerTypes[$expenseType]['sub_items'][] =  Expense::generateRow($expense,$study,$isOneTimeExpense,$expenseType,$revenueCategoriesPerRevenue,$expenseNamesPerCategories,$positionPerDepartments,$increaseYearsFormatted);
          
			$model[$expenseType] = $expensesPerTypes[$expenseType];
        }
		$model[$expenseType]['empty_row'] = Expense::generateRow(null,$study,$isOneTimeExpense,$expenseType,$revenueCategoriesPerRevenue,$expenseNamesPerCategories,$positionPerDepartments,$increaseYearsFormatted);
	  }
	  	
	  	$model['model_id'] = $study->id;
	  	$model['model_name'] ='Study';
	  	$model['expense_type'] ='Expense'; // expense_per_employee , and micro or something
	  	$model['is_by_vuejs'] =true; 
		$model['tableIds'] = [
			'fixed_monthly_repeating_amount',
			'percentage_of_sales',
			'cost_per_unit',
			'one_time_expense',
			'expense_per_employee'
		];
        return response()->json([
           'model'=>$model,
		   'submitUrl'=>route('property.management.store.expenses',['company'=>$company->id,'study'=>$study->id]),
		   'studyStartDate'=>$study->getStudyStartDate(),
           'expenseCategories'=>$expenseCategoriesFormatted,
		   'expenseNamesPerCategories'=>$expenseNamesPerCategories,
		  'revenueCategoriesPerRevenue'=>$revenueCategoriesPerRevenue,
		  'revenueStreams'=>$selectedRevenueStreams,
		   'departments'=>$departments,
		   'positionsPerDepartments'=>$positionPerDepartments,
		   'increaseYearsFormatted'=>$increaseYearsFormatted
		//    'selectedRevenueStreams'=>$selectedRevenueStreams,
   //        'businessUnitsForMultiSelect'=>$businessUnitsForMultiSelect,
 //          'revenueStreamsPerBusinessUnits'=>$revenueStreamsPerBusinessUnits // for selects
        ]);
    }
    protected function getViewVars(Company $company, Study $study)
    {

        return [
            'company'=>$company ,
           'study'=>$study,
            'title'=>__('Expenses'),
 
        ];
    }
  
    
    public function store(
        Company $company,
        StoreExpensesRequest $request,
        Study $study
    ) {
        $modelId = $request->get('model_id');
		$isVuejs = $request->get('is_by_vuejs',false);
        $modelName = $request->get('model_name');
        $expenseType = $request->get('expense_type','Expense');
		$expenseTypes = $request->get('tableIds',[]) ;
		
        $datesAsStringDateIndex = $study->getDatesAsStringAndIndex();
        $operationStartDateAsIndex = $datesAsStringDateIndex[$study->getOperationStartDate()];
        $studyExtendedEndDateAsIndex = Arr::last($datesAsStringDateIndex);
        $studyEndDateAsIndex = $study->getStudyEndDateAsIndex();
    
        $model = ('\App\Models\\PropertyManagement\\'.$modelName)::find($modelId);
        foreach ($expenseTypes as $tableId) {
            #::delete all
            $model->generateRelationDynamically($tableId, $expenseType)->delete();
			$subItems = $isVuejs ?  $request->input($tableId.'.sub_items',[]) : $request->input($tableId,[]);
		
            foreach ($subItems as $tableDataArr) {
                $tableDataArr['study_id'] = $study->id;
				if(isset($tableDataArr['start_date']) && is_array($tableDataArr['start_date'])){ // in case of vuejs
					$tableDataArr['start_date'] = convertJsDateToDB($tableDataArr['start_date']['year'], $tableDataArr['start_date']['month']);
				}
                elseif (isset($tableDataArr['start_date'])  && count(explode('-', $tableDataArr['start_date'])) == 2) {
                    $tableDataArr['start_date'] = $tableDataArr['start_date'].'-01';
                }
				if(isset($tableDataArr['end_date']) && is_array($tableDataArr['end_date'])){ // in case of vuejs
					$tableDataArr['end_date'] = convertJsDateToDB($tableDataArr['end_date']['year'], $tableDataArr['end_date']['month']);
				}
				elseif (isset($tableDataArr['end_date']) &&  count(explode('-', $tableDataArr['end_date'])) == 2) {
                    $tableDataArr['end_date'] = $tableDataArr['end_date'].'-01';
                }
				
                $tableDataArr['expense_type'] = $expenseType;
                $name = $tableDataArr['expense_name_id']??null;
                    
                if (isset($tableDataArr['start_date'])) {
                    $tableDataArr['start_date'] = $datesAsStringDateIndex[$tableDataArr['start_date']];
                } else {
                    $tableDataArr['start_date'] = $operationStartDateAsIndex;
                }
                if (isset($tableDataArr['end_date'])) {
                    $tableDataArr['end_date'] = $datesAsStringDateIndex[$tableDataArr['end_date']];
                } else {
                    $tableDataArr['end_date'] = $operationStartDateAsIndex;
                }
                /**
                 * * to repeat 2 years inside json
                 */
                $loopEndDate = $tableDataArr['end_date'] >=  $studyEndDateAsIndex ? $studyExtendedEndDateAsIndex : $tableDataArr['end_date'];
                $loopEndDate = $loopEndDate ==  0 ? $studyEndDateAsIndex : $loopEndDate ;

                $tableDataArr['relation_name']  = $tableId ;
                $tableDataArr['amount']  = isset($tableDataArr['amount']) ? $tableDataArr['amount'] : 0 ;
                $tableDataArr['monthly_cost_of_unit']  = isset($tableDataArr['monthly_cost_of_unit']) ? $tableDataArr['monthly_cost_of_unit'] : 0 ;
                /**
                 * * Fixed Repeating
                 */
                // $vatRate = $tableDataArr['vat_rate']??0;
                $isDeductible = $tableDataArr['is_deductible'] ?? false;
                if (($tableDataArr['payment_terms']??null) == 'customize') {
                    $tableDataArr['custom_collection_policy'] = sumDueDayWithPayment($tableDataArr['payment_rate'] ?? [], $tableDataArr['due_days'] ?? []);
                }
                if (is_array($isDeductible)) {
                    $tableDataArr['is_deductible'] = $isDeductible[0];
                    $isDeductible= $isDeductible[0];
                }
              
              
                $tableDataArr['company_id']  = $company->id ;
                $tableDataArr['model_id']   = $modelId ;
                $tableDataArr['model_name']   = $modelName ;
                if ($name) {
                    $model->generateRelationDynamically($tableId, $expenseType)->create($tableDataArr);
                }
            }
        }
        // general
	
		$study->recalculateExpenses($modelName , $modelId, $expenseType,$expenseTypes);
		if($request->get('submit_button') == 'save'){
			return response()->json([
				'success'=>true ,
			]);
		}
		return response()->json([
			'redirectTo'=>route('property.management.create.ffe.fixed.assets', ['company'=>$company->id,'study'=>$study->id])
		]);
       
    }

    private function formatDues(array $duesAndDays)
    {
        $result = [];
        foreach ($duesAndDays as $day => $due) {
            $result['due_in_days'][]=$day;
            $result['rate'][]=$due;
        }
        return $result;
    }
    public function calculateStatement(array $expenses, array $vats, array $netPaymentsAfterWithhold, array $withholdPayments, array $dateIndexWithDate, Study $study, float $beginningBalance = 0)
    {
        $expensesForIntervals = [
            'monthly'=>$expenses,
            // 'quarterly'=>sumIntervalsIndexes($expenses, 'quarterly', $study->financialYearStartMonth(), $dateIndexWithDate),
            // 'semi-annually'=>sumIntervalsIndexes($expenses, 'semi-annually', $study->financialYearStartMonth(), $dateIndexWithDate),
            // 'annually'=>sumIntervalsIndexes($expenses, 'annually', $study->financialYearStartMonth(), $dateIndexWithDate),
        ]; 
	//	$dateWithDateIndex = $study->getDateWithDateIndex();
		// $datesForIntervals = [
        //     'monthly'=>$dateWithDateIndex,
        //     // 'quarterly'=>sumIntervalsIndexes($dateWithDateIndex, 'quarterly', $study->financialYearStartMonth(), $dateIndexWithDate),
        //     // 'semi-annually'=>sumIntervalsIndexes($dateIndexWithDate, 'semi-annually', $study->financialYearStartMonth(), $dateIndexWithDate),
        //     // 'annually'=>sumIntervalsIndexes($dateIndexWithDate, 'annually', $study->financialYearStartMonth(), $dateIndexWithDate),
        // ];
        $netPaymentAfterWithholdForInterval = [
            'monthly'=>$netPaymentsAfterWithhold,
            // 'quarterly'=>sumIntervalsIndexes($netPaymentsAfterWithhold, 'quarterly', $study->financialYearStartMonth(), $dateIndexWithDate),
            // 'semi-annually'=>sumIntervalsIndexes($netPaymentsAfterWithhold, 'semi-annually', $study->financialYearStartMonth(), $dateIndexWithDate),
            // 'annually'=>sumIntervalsIndexes($netPaymentsAfterWithhold, 'annually', $study->financialYearStartMonth(), $dateIndexWithDate),
        ];
        
        $result = [];
        foreach (['monthly'=>__('Monthly')] as $intervalName=>$intervalNameFormatted) {
        // foreach (getIntervalFormatted() as $intervalName=>$intervalNameFormatted) {
            $beginningBalance = 0;
            foreach ($dateIndexWithDate as $dateIndex=>$dateAsString) {
				$currentExpenseValue = $expensesForIntervals[$intervalName][$dateIndex]??0 ;
                $date = $dateIndex;
                $result[$intervalName]['beginning_balance'][$date] = $beginningBalance;
                $currentVat = $vats[$date]??0 ;
                $totalDue[$date] =  $currentExpenseValue+$currentVat+$beginningBalance;
                $paymentAtDate = $netPaymentAfterWithholdForInterval[$intervalName][$date]??0 ;
                $withholdPaymentAtDate = $withholdPayments[$date]?? 0 ;
                $endBalance[$date] = $totalDue[$date] - $paymentAtDate  - $withholdPaymentAtDate ;
                $beginningBalance = $endBalance[$date] ;
                $result[$intervalName]['expense'][$date] =  $currentExpenseValue ;
                $result[$intervalName]['vat'][$date] =  $currentVat ;
                $result[$intervalName]['total_due'][$date] = $totalDue[$date];
                $result[$intervalName]['payment'][$date] = $paymentAtDate;
                $result[$intervalName]['withhold_amount'][$date] = $withholdPaymentAtDate;
                $result[$intervalName]['end_balance'][$date] =$endBalance[$date];
            }
        }
        return $result;
    
        
    }
    public function getExpenseNamesForCategory(Company $company, Request $request)
    {
        $categoryId =  $request->get('expenseCategoryId');
        $result = ExpenseName::where('company_id', $company->id)->where('expense_type', $categoryId)->orderBy('name')->get();
        return response()->json([
            'status'=>true ,
            'data'=>$result
        ]);
    }
	public function getExpenseNamesForCategoryOnlyBranches(Company $company, Request $request)
    {
        $categoryId =  $request->get('expenseCategoryId');
        $result = ExpenseName::where('company_id', $company->id)->where('expense_type', $categoryId)->where('is_branch_expense',1)->orderBy('name')->get();
        return response()->json([
            'status'=>true ,
            'data'=>$result
        ]);
    }
    public function getExpenseNamesForCategoryOnlyEmployees(Company $company, Request $request)
    {
        $categoryId =  $request->get('expenseCategoryId');
        $result = ExpenseName::where('company_id', $company->id)->where('is_employee_expense', 1)->where('expense_type', $categoryId)->orderBy('name')->get();
        return response()->json([
            'status'=>true ,
            'data'=>$result
        ]);
    }
}
