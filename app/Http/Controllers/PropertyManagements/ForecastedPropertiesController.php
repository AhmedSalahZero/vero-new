<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PropertyManagement\ExpenseName;
use App\Models\PropertyManagement\ForecastedProperty;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyDueInstallment;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Illuminate\Http\Request;

class ForecastedPropertiesController extends Controller
{
    use PropertyManagement ;
    public function create(Company $company, Request $request, Study $study)
    {
        return view('property_managements.forecasted-properties.form', $this->getViewVars($company, $study));
    }
     public function getOldData(Company $company, Request $request, Study $study)
    {
		
	$forecastedProperties = $study->forecastedProperties;
	$forecastedPropertiesFormatted=[];
	  	foreach(count($forecastedProperties) ? $forecastedProperties : [null] as $forecastedProperty){
			$forecastedPropertiesFormatted[] = ForecastedProperty::generateRow($forecastedProperty,$company->id,$study->id);
		}
		$studyId = $study->id;
        return response()->json([
           'model'=>[
			'forecastedProperties'=>$forecastedPropertiesFormatted,
		   ],
		   'empty_rows'=>[
			'forecastedProperties'=>ForecastedProperty::generateRow(null,$company->id,$study->id),
			'forecastedDueInstallments'=>(new ForecastedProperty())->getForecastedDueInstallmentsFormatted($studyId),
			'regular_installments_amounts' => PropertyDueInstallment::getDefaultRegularInstallmentAmounts(),
		   ],
		   'submitUrl'=>route('property.management.store.forecasted.properties',['company'=>$company->id,'study'=>$study->id]),
		   'studyStartDate'=>$study->getStudyStartDate(),
		   'selects'=>[
			'categories'=>$company->getPropertyCategoriesFormatted(),
		   ],
        //    'expenseCategories'=>$expenseCategoriesFormatted,
		//    'expenseNamesPerCategories'=>$expenseNamesPerCategories,
		//    'revenueCategoriesPerRevenue'=>$revenueCategoriesPerRevenue,
		//    'revenueStreams'=>$selectedRevenueStreams,
		//    'departments'=>$departments,
		//    'positionsPerDepartments'=>$positionPerDepartments,
		//    'increaseYearsFormatted'=>$increaseYearsFormatted
		
        ]);
    }
    protected function getViewVars(Company $company, Study $study)
    {

        return [
            'company'=>$company ,
           'study'=>$study,
            'title'=>__('Forecasted Properties Investments'),
        ];
    }
  
    
    public function store(
        Company $company,
        Request $request,
        Study $study
    ) {
		$study->removeForecastedProperties();
		$study->removeAreaPropertiesForRevenueStreamType(Property::PROPERTY_FORECASTED);
		foreach($request->get('forecastedProperties') as $forecastedPropertyArr){
			unset($forecastedPropertyArr['id']);
			$forecastedPropertyArr['forecastedDueInstallment']['company_id'] = $company->id;
			$forecastedProperty=$study->forecastedProperties()->create($forecastedPropertyArr);
			$forecastedProperty->forecastedDueInstallment()->create($forecastedPropertyArr['forecastedDueInstallment']);
			$forecastedProperty->calculateToBeDeliveredRentRevenue();
			$study->createNewAreaProperty($forecastedProperty['id'],Property::PROPERTY_FORECASTED,$forecastedProperty['area']);
		}
		$formattedResult = [];
		ForecastedProperty::getForecastedPropertiesCoveragesAmounts($study, $formattedResult);
		$forecastedProperty->calculatePropertyDueInstallments();
		ForecastedProperty::recalculateDueInstallments($study);
	//	$study->recalculateDueInstallments();
		$study->updateExpensesPercentageAndCostPerUnitsOfSales();
		
		if($request->get('submit_button') == 'save'){
			return response()->json([
				'success'=>true,
				'redirectTo'=>route('property.management.create.forecasted.properties', ['company'=>$company->id,'study'=>$study->id])
			]);
		}
		return response()->json([
			'redirectTo'=>route('view.manpower.for.property.management', ['company'=>$company->id,'study'=>$study->id])
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
