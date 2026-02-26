<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Helpers\HHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpensesRequest;
use App\Models\Company;
use App\Models\PropertyManagement\Expense;
use App\Models\PropertyManagement\ExpenseName;
use App\Models\PropertyManagement\Position;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyExpense;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Arr;
use Illuminate\Http\Request;

class PropertyExpensesController extends Controller
{
    use PropertyManagement ;
    public function create(Company $company, Request $request, Property $property)
    {
        return view('property_managements.property-expenses.form', $this->getViewVars($company, $property));
    }
     public function getOldData(Company $company, Request $request, Property $property)
    {
		
	
	
        $expenseCategoriesFormatted = ExpenseName::getExpenseCategoriesFormatted();
		$expenseNamesPerCategories = ExpenseName::getExpenseNamesPerCategories();
		
		  
       
        // $revenueStreamsPerBusinessUnits = [];
		//	$isOneTimeExpense = $expenseType == 'one_time_expense'; 
		//   $propertyExpenses = PropertyExpense::where('property_id', $property->id)->get();
		//   $model = [
		// 	//'emptyRow'=>PropertyExpense::generateRow(null,$property,$expenseNamesPerCategories),
		//   ];
		$propertyExpenses = $property->propertyExpenses ;
		$model = null ;
		foreach(  [null]   as $propertyExpense){
		  $model['sub_items'][] =  PropertyExpense::generateRow($propertyExpense,$property,$expenseNamesPerCategories);
		}
	
	$rows = $property->propertyExpenses->map(function(PropertyExpense $row){
		return [
			'id'=>$row->id,
			'expense_category'=>$row->expense_category,
			'expense_category_name'=>$row->getExpenseCategoryName(),
			'expense_name_id'=>$row->expense_name_id,
			'expense_name'=>$row->getExpenseName(),
			'amount'=>$row->amount,
			'date'=>$row->date,
			'dateFormatted'=>$row->date,
			'payment_date'=>$row->payment_date,
			'is_paid'=>$row->isPaid(),
			'note'=>$row->note,
		];
	})->toArray();

	$latestRunningContract = $property->getLatestRunningContract();
	$rentRevenueSumToDate = $latestRunningContract ? $latestRunningContract->getSumRentRevenuesToDate() : 0;
	$rentCollectionSumToDate = $latestRunningContract ? $latestRunningContract->getSumRentCollectionsToDate() : 0;

	// ->only(['id','expense_category','expense_name_id','amount','date','payment_date','is_paid','note'])
        return response()->json([
           'model'=>$model,
		   'rows'=>$rows,
		   'emptyRow'=>PropertyExpense::generateRow(null,$property,$expenseNamesPerCategories),
		   'submitUrl'=>route('property.management.store.property.expenses',['company'=>$company->id,'property'=>$property->id]),
		   'rentRevenueSumToDate'=>$rentRevenueSumToDate,
		   'rentCollectionSumToDate'=>$rentCollectionSumToDate,
		 //  'studyStartDate'=>$study->getStudyStartDate(),
           'expenseCategories'=>$expenseCategoriesFormatted,
		   'expenseNamesPerCategories'=>$expenseNamesPerCategories,
		  // 'revenueCategoriesPerRevenue'=>$revenueCategoriesPerRevenue,
		  // 'revenueStreams'=>$selectedRevenueStreams,
		  // 'departments'=>$departments,
		  // 'positionsPerDepartments'=>$positionPerDepartments,
		 //  'increaseYearsFormatted'=>$increaseYearsFormatted
		//    'selectedRevenueStreams'=>$selectedRevenueStreams,
   //        'businessUnitsForMultiSelect'=>$businessUnitsForMultiSelect,
 //          'revenueStreamsPerBusinessUnits'=>$revenueStreamsPerBusinessUnits // for selects
        ]);
    }
    protected function getViewVars(Company $company, Property $property)
    {

        return [
            'company'=>$company ,
           'property'=>$property,
            'title'=>__('Property Expenses'),
 
        ];
    }
  
    
    public function store(
        Company $company,
        StoreExpensesRequest $request,
        Property $property
    ) {
		if($request->get('in_edit_mode')){
			$propertyExpense = $request->input('sub_items.0',[]);
			$propertyExpense = PropertyExpense::find($propertyExpense['id'])->update($propertyExpense);

		}else{
			$propertyExpenses = $request->get('sub_items',[]);
			foreach($propertyExpenses as $propertyExpense){
				$propertyExpense = PropertyExpense::create($propertyExpense);
			}
			
		}
		// $id = $request->get('id');
		// $propertyExpense = PropertyExpense::find($id);
		// if($propertyExpense){
		// 	$propertyExpense->update($propertyExpenseData);
		// }else{
		// 	$propertyExpense = PropertyExpense::create($propertyExpenseData);
		// }
		
		if($request->get('submit_button') == 'save'){
			return response()->json([
				'redirectTo'=>route('property.management.create.property.expenses', ['company'=>$company->id,'property'=>$property->id])
			]);
		}
		
		return response()->json([
			'redirectTo'=>route('property.management.view.properties', ['company'=>$company->id])
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
	public function destroy(Company $company, Property $property, PropertyExpense $propertyExpense)
    {

        $propertyExpense->delete();
        return response()->json([
            'status'=>true ,
            'message'=>__('Property expense deleted successfully')
        ]);
    }
}
