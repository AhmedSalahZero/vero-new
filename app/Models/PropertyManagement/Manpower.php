<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int|null $branch_id
 * @property string $type general , branch , all-branches
 * @property int|null $position_id
 * @property int $existing_count
 * @property numeric $monthly_net_salary
 * @property array<array-key, mixed>|null $hiring_counts
 * @property int $company_id
 * @property int|null $study_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $manpower_salaries
 * @property array<array-key, mixed>|null $accumulated_manpower_counts
 * @property array<array-key, mixed>|null $salary_expenses
 * @property array<array-key, mixed>|null $salary_payments
 * @property array<array-key, mixed>|null $tax_and_social_insurance_statement
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Position|null $position
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereAccumulatedManpowerCounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereExistingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereHiringCounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereManpowerSalaries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereMonthlyNetSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereSalaryExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereSalaryPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereTaxAndSocialInsuranceStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Manpower whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Manpower extends Model
{
	use HasCollectionOrPaymentStatement;
	use BelongsToStudy,BelongsToCompany;
	const GENERAL ='general';
	protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
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
		$salaryExpenses = DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('manpowers')
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
	public function getHiringCountsFormatted(array $dates):array 
	{
		$result = [];
		foreach ($dates as $dateIndex => $date) {
			$result[$dateIndex] = $this->getHiringCountsAtDateIndex($dateIndex);
		}
		return $result;
	
	}
	
}
