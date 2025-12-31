<?php

namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\IsDepartment;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class ExpenseName extends Model
{
	use BelongsToStudy,BelongsToCompany,IsDepartment,HasBasicStoreRequest;
	protected $table ='expense_names';
	protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
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

	
}
