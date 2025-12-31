<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Manpower extends Model
{
	use HasCollectionOrPaymentStatement;
	use BelongsToStudy,BelongsToCompany;
	protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
 	protected $guarded = ['id'];
	protected $casts = [
		'hiring_counts'=>'array',
		'salary_payments'=>'array',
		'accumulated_manpower_counts'=>'array',
		'salary_expenses'=>'array',
		'tax_and_social_insurance_statement'=>'array',
	];
	public function position():BelongsTo
	{
		return $this->belongsTo(Position::class,'position_id','id');
	}

	public function getExistingCount():int 
	{
		return $this->existing_count??0;
	}
	public function getMonthlyNetSalary()
	{
		return $this->monthly_net_salary;
	}
	public function getHiringCounts():array
	{
		return (array)$this->hiring_counts;
	} 
	public function getHiringCountsAtDateIndex(int $dateIndex)
	{
		return $this->getHiringCounts()[$dateIndex]??0;
	}
	
	public function getSalaryPayments():array
	{
		return $this->salary_payments;
	} 
	public function getSalaryPaymentsAtDateIndex(int $dateIndex)
	{
		return $this->getSalaryPayments()[$dateIndex];
	}
	
	public function getAccumulatedManpowerCounts():array
	{
		return $this->accumulated_manpower_counts;
	} 
	public function getAccumulatedManpowerCountsAtDateIndex(int $dateIndex)
	{
		return $this->getAccumulatedManpowerCounts()[$dateIndex];
	}
	public static function getSalaryExpensesPerCategory(array $monthsWithItsYear,int $studyId,int $companyId)
	{
		   $salaryExpensesForCategory = [];
		$salaryExpenses = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('manpowers')
					->join('positions','manpowers.position_id','=','positions.id')
					->join('departments','positions.department_id','=','departments.id')
					->where('manpowers.company_id',$companyId)
					->where('study_id',$studyId)
					// ->where('departments.type','manpower')
					->selectRaw('expense_type,salary_expenses')->get();
        foreach ($salaryExpenses as $salaryExpense) {
            $expenseCategory = $salaryExpense->expense_type;
            $salaryExpensePayload = (array)json_decode($salaryExpense->salary_expenses);
            $salaryExpensePayload = $salaryExpensePayload ? $salaryExpensePayload : [];
            foreach ($monthsWithItsYear as $monthIndex => $yearIndex) {
                
                $currentSalaryExpense = $salaryExpensePayload[$monthIndex]??0;
                $salaryExpensesForCategory[$expenseCategory][$monthIndex] = isset($salaryExpensesForCategory[$expenseCategory][$monthIndex]) ?  $salaryExpensesForCategory[$expenseCategory][$monthIndex] + $currentSalaryExpense : $currentSalaryExpense;
            }
        }
		return $salaryExpensesForCategory;
	}	
	
	
}
