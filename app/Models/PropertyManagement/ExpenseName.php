<?php

namespace App\Models\PropertyManagement;

use App\Helpers\HStr;
use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\IsDepartment;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $company_id
 * @property string|null $expense_type
 * @property string $name
 * @property int $is_employee_expense
 * @property int $is_branch_expense
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName whereExpenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName whereIsBranchExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName whereIsEmployeeExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\ExpenseName whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExpenseName extends Model
{
	use BelongsToStudy,BelongsToCompany,HasBasicStoreRequest;
	protected $table ='expense_names';
	protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
 	protected $guarded = ['id'];
	const EXPENSE = 'expense';
	 public static function boot()
	 {
		 parent::boot();
		 static::saving(function($row){
			$row->is_branch_expense = $row->is_branch_expense[0]??0;
			$row->is_employee_expense = $row->is_employee_expense[0]??0;
		 });
	 }
	 public function getName()
	{
		return $this->name ;
	}
	public function getExpenseType(): string
	{
		return $this->expense_type;
	}
	public function isEmployeeExpense():bool
	{
		return (bool)$this->is_employee_expense;
	}
	
	public function isBranchExpense():bool
	{
		return (bool)$this->is_branch_expense;
	}
	public static function getCategories(Company $company)
	{
		return ExpenseName::where('company_id',$company->id)->orderBy('expense_type','asc')->pluck('expense_type','expense_type')->unique()->toArray();
	}
	public static function getCategoriesForBranch(Company $company)
	{
		return ExpenseName::where('company_id',$company->id)->where('is_branch_expense',1)->orderBy('expense_type','asc')->pluck('expense_type','expense_type')->unique()->toArray();
	}
	public static function getCategoriesForEmployee(Company $company)
	{
		return ExpenseName::where('company_id',$company->id)->where('is_employee_expense',1)->orderBy('expense_type','asc')->pluck('expense_type','expense_type')->unique()->toArray();
	}
	/**
 * * For vuejs
 */
public static function getExpenseCategoriesFormatted():array
{
    $results = [];
    $expenseCategories = self::getCategories(app(Company::class));
    foreach ($expenseCategories as $type => $name) {
        $results[] = [
            'title'=>HStr::camelizeWithSpace($type) ,
            'id'=>$type
        ];
    }
    return $results;
}
	
public static function getExpenseNamesPerCategories():array
{
	 $results = [];
	 $company = app(Company::class); 
    $expenseCategories = self::getCategories($company);
    foreach ($expenseCategories as $type => $name) {
        $expenseNames = self::where('expense_type',$type)->where('company_id',$company->id)->get();
		foreach($expenseNames as $expenseName){
			$results[$type][] = [
				'id'=>$expenseName->id , 
				'title'=>$expenseName->name
			];
		}
    }
    return $results;
}

	
}
