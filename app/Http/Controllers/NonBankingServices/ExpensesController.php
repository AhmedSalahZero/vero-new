<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpensesRequest;
use App\Models\Company;
use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\ExpenseName;
use App\Models\NonBankingService\Position;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Arr;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    use NonBankingService ;
    public function create(Company $company, Request $request, Study $study)
    {
        return view('non_banking_services.expenses.form', $this->getViewVars($company, $study));
    }
     public function expensesGetVueOldData(Company $company, Request $request, Study $study)
    {
    
		
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

		
		$selectedRevenueStreams = $study->getSelectedRevenueStreamTypesFormatted();
		$revenueCategoriesPerRevenue = [];
		foreach(array_column($selectedRevenueStreams,'id') as $revenueId){
			$revenueCategoriesPerRevenue[$revenueId] = $study->getSelectedRevenueStreamWithCategories([$revenueId]);
		}
		
        $expenseCategoriesFormatted = getExpenseCategoriesFormatted();
		$expenseNamesPerCategories = getExpenseNamesPerCategories();
		
		  
       
        $revenueStreamsPerBusinessUnits = [];
      foreach(getTypesForValuesForNonBanking() as $expenseType => $expenseArr){
			$isOneTimeExpense = $expenseType == 'one_time_expense'; 
		  $expenses = Expense::where('study_id', $study->id)->where('relation_name', $expenseType)->get();
			
		 foreach (count($expenses) ? $expenses : [null] as $expense) {
			/**
			 * @var Expense $expense
			 */
            if ($expense) {
                $revenueStreamsPerBusinessUnits[$expense->id] = [];
            }
            $expensesPerTypes[$expenseType]['sub_items'][] =  Expense::generateRow($expense,$study,$isOneTimeExpense,$expenseType,$revenueCategoriesPerRevenue,$expenseNamesPerCategories,$positionPerDepartments);
          
			$model[$expenseType] = $expensesPerTypes[$expenseType];
        }
		$model[$expenseType]['empty_row'] = Expense::generateRow(null,$study,$isOneTimeExpense,$expenseType,$revenueCategoriesPerRevenue,$expenseNamesPerCategories,$positionPerDepartments);
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
		// dd($departmentsFormatted);
        return response()->json([
           'model'=>$model,
	
		   'submitUrl'=>route('store.expenses',['company'=>$company->id,'study'=>$study->id]),
		   'studyStartDate'=>$study->getStudyStartDate(),
           'expenseCategories'=>$expenseCategoriesFormatted,
		   'expenseNamesPerCategories'=>$expenseNamesPerCategories,
		   'revenueCategoriesPerRevenue'=>$revenueCategoriesPerRevenue,
		   'revenueStreams'=>$selectedRevenueStreams,
		   'departments'=>$departments,
		   'positionsPerDepartments'=>$positionPerDepartments
		//    'selectedRevenueStreams'=>$selectedRevenueStreams,
   //        'businessUnitsForMultiSelect'=>$businessUnitsForMultiSelect,
 //          'revenueStreamsPerBusinessUnits'=>$revenueStreamsPerBusinessUnits // for selects
        ]);
    }
    protected function getViewVars(Company $company, Study $study)
    {
   //     $selectedRevenueStreams = $study->getSelectedRevenueStreamTypesFormatted();
        return [
       //     'selectedRevenueStreams'=>$selectedRevenueStreams,
            'company'=>$company ,
          //  'type'=>'create',
            // 'revenueStreams'=>$revenueStreams,
           'study'=>$study,
         //   'model'=>$study ,
        //    'expenseType'=>HHelpers::getClassNameWithoutNameSpace((new Expense())),
            'title'=>__('Expenses'),
     //       'storeRoute'=>route('store.expenses', ['company'=>$company->id , 'study'=>$study->id]),
      //      'yearsWithItsMonths' => $study->getOperationDurationPerYearFromIndexes(),
        //    'revenueStreamTypes'=>$study->getCheckedRevenueStreamTypesForSelect()
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
		
        // $studyId = $study->id;
        $datesAsStringDateIndex = $study->getDatesAsStringAndIndex();
        // $datesAsIndexAndString = array_flip($datesAsStringDateIndex);
        $operationStartDateAsIndex = $datesAsStringDateIndex[$study->getOperationStartDate()];
        $studyExtendedEndDateAsIndex = Arr::last($datesAsStringDateIndex);
        $studyEndDateAsIndex = $study->getStudyEndDateAsIndex($datesAsStringDateIndex, $study->getStudyEndDate());
    
        $model = ('\App\Models\\NonBankingService\\'.$modelName)::find($modelId);
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

                // $monthsAsIndexes = range(0, $studyEndDateAsIndex) ;
                $tableDataArr['relation_name']  = $tableId ;
                $tableDataArr['amount']  = isset($tableDataArr['amount']) ? $tableDataArr['amount'] : 0 ;
                $tableDataArr['monthly_cost_of_unit']  = isset($tableDataArr['monthly_cost_of_unit']) ? $tableDataArr['monthly_cost_of_unit'] : 0 ;
                /**
                 * * Fixed Repeating
                 */
                // $vatRate = $tableDataArr['vat_rate']??0;
                $isDeductible = $tableDataArr['is_deductible'] ?? false;
                if ($tableDataArr['payment_terms']??null == 'customize') {
                    $tableDataArr['custom_collection_policy'] = sumDueDayWithPayment($tableDataArr['payment_rate'], $tableDataArr['due_days']);
                }
                // $customCollectionPolicy = $tableDataArr['custom_collection_policy']??[];
                if (is_array($isDeductible)) {
                    $tableDataArr['is_deductible'] = $isDeductible[0];
                    $isDeductible= $isDeductible[0];
                }
                // $isFixedRepeating = isset($tableDataArr['amount']) && $tableId == 'fixed_monthly_repeating_amount';
                // $isExpensePerEmployee = (isset($tableDataArr['monthly_cost_of_unit']) && $tableId == 'expense_per_employee') ;
                // $isCostPerUnit = (isset($tableDataArr['monthly_cost_of_unit']) && $tableId == 'cost_per_unit') ;
                // $revenueStreamTypes = $tableDataArr['revenue_stream_type']??[] ;
                // $categoryIds = $tableDataArr['stream_category_ids']??[] ;
                
              
                /**
                 * * Expense As Percentage
                 */
                // if ($tableId =='percentage_of_sales' || $tableId =='expense_as_percentage') {
                //     $expenseAsPercentageResults = $expenseAsPercentageEquation->calculate($studyId, $tableDataArr['percentage_of'], $revenueStreamTypes, $categoryIds, $tableDataArr['start_date'], $loopEndDate, $tableDataArr['monthly_percentage'], $tableDataArr['payment_terms'], $vatRate, $isDeductible, $tableDataArr['withhold_tax_rate']) ;
                //     $tableDataArr['expense_as_percentages']  =$expenseAsPercentageResults['total_before_vat']  ;
                //     $tableDataArr['total_vat']  =$expenseAsPercentageResults['total_vat']  ;
                //     $tableDataArr['total_after_vat']  =$expenseAsPercentageResults['total_after_vat']  ;
                //     $withholdAmounts  = $expenseAsPercentageResults['total_withhold'];
                //     $tableDataArr['payment_amounts'] = $study->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $tableDataArr['total_after_vat'], $datesAsIndexAndString, $customCollectionPolicy) ;
                //     $payments = $study->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $tableDataArr['total_after_vat'], $datesAsIndexAndString, $customCollectionPolicy,true) ;
                //     $withholdPayments = $study->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $withholdAmounts, $datesAsIndexAndString, $customCollectionPolicy) ;
                //     $netPaymentsAfterWithhold = HArr::subtractAtDates([$payments,$withholdPayments], $dateWithDateIndex);
                //     $tableDataArr['withhold_amounts'] = $withholdAmounts ;
                //     $tableDataArr['withhold_payments']=$withholdPayments;
                //     $tableDataArr['payment_amounts'] = $payments;
                //     $tableDataArr['net_payments_after_withhold']=$netPaymentsAfterWithhold;
				// 	$tableDataArr['withhold_statements']=$study->calculateWithholdStatement($withholdPayments , 0 , $dateIndexWithDate);
					

                //     $tableDataArr['collection_statements']   =$this->calculateStatement($tableDataArr['expense_as_percentages'], $tableDataArr['total_vat'], $netPaymentsAfterWithhold, $withholdPayments, $dateIndexWithDate, $study);
                    
                // }
                /**
                 * * One Time Expense
                */
                // if ($tableId == 'one_time_expense') {
                //     $startDateAsIndex = $tableDataArr['start_date'] ;
                //     $amountBeforeVat = $tableDataArr['amount'] ;
                //     $withholdAmount = $tableDataArr['withhold_tax_rate'] / 100 * $amountBeforeVat ;
                //     $amortizationMonths = $tableDataArr['amortization_months']??12 ;
                //     $oneTimeExpenses = $oneTimeExpenseEquation->calculate($amountBeforeVat, $amortizationMonths, $startDateAsIndex, $isDeductible, $vatRate);
                //     $tableDataArr['payload']  = $oneTimeExpenses ;
                //     $amountBeforeVatPayload = [$startDateAsIndex=>$amountBeforeVat] ;
                //     $vatRate = $tableDataArr['vat_rate'] / 100 ;
                //     $vats = [$startDateAsIndex=>$amountBeforeVat * $vatRate];
                    
                //     $tableDataArr['total_vat']  =$vats  ;
                //     $amountAfterVat = [$startDateAsIndex => $amountBeforeVat + $amountBeforeVat * $vatRate ];
                //     $tableDataArr['total_after_vat']  =$amountAfterVat  ;
                //     $withholdAmount = $tableDataArr['withhold_tax_rate']/100 ;
                //     $withholdAmounts  = [$startDateAsIndex =>  $amountBeforeVat * $withholdAmount ] ;
                //     $payments = $study->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $amountAfterVat, $datesAsIndexAndString, $customCollectionPolicy, true) ;
                //     $withholdPayments = $study->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $withholdAmounts, $datesAsIndexAndString, $customCollectionPolicy) ;
                //     $netPaymentsAfterWithhold = HArr::subtractAtDates([$payments,$withholdPayments], $dateWithDateIndex);
                //     $tableDataArr['withhold_amounts'] = $withholdAmounts ;
                //     $tableDataArr['withhold_payments']=$withholdPayments;
                //     $tableDataArr['payment_amounts'] = $payments;
                //     $tableDataArr['net_payments_after_withhold']=$netPaymentsAfterWithhold;
				// 	$tableDataArr['withhold_statements']=$study->calculateWithholdStatement($withholdPayments , 0 , $dateIndexWithDate);
				//      $tableDataArr['collection_statements']   =$this->calculateStatement($amountBeforeVatPayload, $tableDataArr['total_vat'], $netPaymentsAfterWithhold, $withholdPayments, $dateIndexWithDate, $study);
                // }
              
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
		
		return response()->json([
			'redirectTo'=>route('create.ffe.fixed.assets', ['company'=>$company->id,'study'=>$study->id])
		]);
        // if ($request->get('saveAndContinue')) {
        //     return response()->json([
        //         'redirectTo'=>route('create.ffe.fixed.assets', ['company'=>$company->id,'study'=>$study->id])
        //     ]);
        // }
        // return response()->json([
        //     'redirectTo'=>route('create.expenses', ['company'=>$company->id,'study'=>$study->id])
        // ]);
        
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
		$dateWithDateIndex = $study->getDateWithDateIndex();
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
