<?php

namespace App\Models\FinancialPlanning;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $category_name
 * @property int|null $start_date
 * @property string|null $interval
 * @property string|null $monthly_cost_of_unit
 * @property string|null $percentage_of
 * @property array<array-key, mixed>|null $revenue_stream_type
 * @property string|null $month_percentage
 * @property string|null $payment_terms
 * @property string|null $vat_rate
 * @property int $is_deductible
 * @property string|null $withhold_tax_rate
 * @property string|null $increase_rate
 * @property string|null $increase_interval
 * @property numeric $amount
 * @property array<array-key, mixed>|null $monthly_repeating_amounts
 * @property array<array-key, mixed>|null $payload
 * @property int $model_id
 * @property string|null $model_name
 * @property string|null $expense_type
 * @property string|null $relation_name
 * @property string|null $allocation_base_1
 * @property string|null $allocation_base_2
 * @property string|null $allocation_base_3
 * @property string|null $conditional_to
 * @property string|null $conditional_value_a
 * @property string|null $conditional_value_b
 * @property array<array-key, mixed>|null $custom_collection_policy
 * @property int $company_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $department_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereAllocationBase1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereAllocationBase2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereAllocationBase3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereConditionalTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereConditionalValueA($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereConditionalValueB($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereCustomCollectionPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereExpenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereIncreaseInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereIncreaseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereIsDeductible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereModelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereMonthPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereMonthlyCostOfUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereMonthlyRepeatingAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense wherePercentageOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereRelationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereRevenueStreamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereVatRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Expense whereWithholdTaxRate($value)
 * @mixin \Eloquent
 */
class Expense extends Model
{
	use BelongsToStudy,BelongsToCompany;
	protected $guarded = ['id'];
	protected $connection =FINANCIAL_PLANNING_CONNECTION_NAME;
	protected $casts = [
		'monthly_repeating_amounts'=>'array',
		'expense_as_percentages'=>'array',
		'payload'=>'array',
		'custom_collection_policy'=>'array',
		'revenue_stream_type'=>'array',
		'stream_category_ids'=>'array',
		
	];
		
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}
	public function model()
	{
		$modelName = '\App\Models\\'.$this->model_name ;
		return $this->belongsTo($modelName , 'model_id','id');
		
	}
	public function getName()
	{
		return $this->name ;
	}
	public function getCategoryName()
	{
		return $this->category_name ;
	}
	public function getStartDateAsIndex()
	{
		return $this->start_date;
	}
	public function getStartDateFormatted()
	{
		return app('dateIndexWithDate')[$this->start_date];
	}
	public function getEndDateAsIndex()
	{
		return $this->end_date;
	}
	public function getEndDateFormatted()
	{
		return !is_null($this->end_date) ? app('dateIndexWithDate')[$this->end_date] : null;
	}
	// public function getMonthlyAmount()
	// {
	// 	return $this->monthly_amount ?: 0 ;
	// }
	public function getPaymentTerm()
	{
		return $this->payment_terms ;
	}
	public function getVatRate()
	{
		return $this->vat_rate ?: 0;
	}
	public function getWithholdTaxRate()
	{
		return $this->withhold_tax_rate?:0;
	}	
	public function getIncreaseRate()
	{
		return $this->increase_rate ?: 0;
		
	}
	public function getIncreaseInterval()
	{
		return $this->increase_interval ;
	}
	public function getPayloadAtDate(string $date)
	{
		
		return $this->payload[$date] ?? 0 ;
	}

public function getRevenueStreamTypes():array
{
	return (array)$this->revenue_stream_type ;
}	
public function getMonthlyPercentage()
{
	return $this->monthly_percentage ?:0;
}
public function getMonthlyCostOfUnit()
{
	return $this->monthly_cost_of_unit ?:0;
}
public function getDepartment()
{
	// this must be multiple 
	return '';
}
public function getEmployee()
{
	// this must be multiple 
	return '';
}
public function getInterval()
{
	return $this->interval ;
}
// public function getAllocationBaseOne()
// {
// 	return $this->allocation_base_1 ; 
// }
// public function getAllocationBaseTwo()
// {
// 	return $this->allocation_base_2; 
// }

// public function getAllocationBaseThree()
// {
// 	return $this->allocation_base_3; 
// }
// 	public function getConditionalTo()
// 	{
// 		return $this->conditional_to ;
// 	}
// 	public function getConditionalValueA()
// 	{
// 		return $this->conditional_value_a ;
// 	}
// 	public function getConditionalValueB()
// 	{
// 		return $this->conditional_value_b ;
// 	}
	public function getPaymentRate(int $rateIndex){
		return array_values($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
	}
	public function getPaymentRateAtDueInDays($rateIndex)
	{
		return array_keys($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ; 
	}
	public function isDeductible()
	{
		return $this->is_deductible;
	}
	public function getAmount()
	{
		return $this->amount ?: 0 ;
	}
	public function getExpenseCategory()
	{
		return $this->expense_category ;
	}
	public function getPercentageOf()
	{
		return $this->percentagE_of;
	}
	public function getStreamCategoryIds():array
	{
		return (array)$this->stream_category_ids;
	}
	
}
