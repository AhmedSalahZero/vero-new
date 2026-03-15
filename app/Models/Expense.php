<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $category_name
 * @property string|null $start_date
 * @property string|null $interval
 * @property string|null $monthly_cost_of_unit
 * @property string|null $revenue_stream_type
 * @property string|null $monthly_amount
 * @property string|null $month_percentage
 * @property string|null $payment_terms
 * @property string|null $vat_rate
 * @property int $is_deductible
 * @property string|null $withhold_tax_rate
 * @property string|null $increase_rate
 * @property string|null $increase_interval
 * @property array<array-key, mixed>|null $payload
 * @property int $model_id
 * @property string|null $model_name
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
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereAllocationBase1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereAllocationBase2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereAllocationBase3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereConditionalTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereConditionalValueA($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereConditionalValueB($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereCustomCollectionPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereIncreaseInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereIncreaseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereIsDeductible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereModelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereMonthPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereMonthlyAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereMonthlyCostOfUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereRelationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereRevenueStreamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereVatRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Expense whereWithholdTaxRate($value)
 * @mixin \Eloquent
 */
class Expense extends Model
{
	
	protected $guarded = ['id'];
	
	protected $casts = [
		'payload'=>'array',
		'custom_collection_policy'=>'array',
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
	public function getStartDateFormatted()
	{
		return $this->start_date ;
	}
	public function getMonthlyAmount()
	{
		return $this->monthly_amount ?: 0 ;
	}
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

public function getRevenueStreamType()
{
	return $this->revenue_stream_type ;
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
public function getAllocationBaseOne()
{
	return $this->allocation_base_1 ; 
}
public function getAllocationBaseTwo()
{
	return $this->allocation_base_2; 
}

public function getAllocationBaseThree()
{
	return $this->allocation_base_3; 
}
	public function getConditionalTo()
	{
		return $this->conditional_to ;
	}
	public function getConditionalValueA()
	{
		return $this->conditional_value_a ;
	}
	public function getConditionalValueB()
	{
		return $this->conditional_value_b ;
	}
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
	public function getExpenseCategory()
	{
		return $this->expense_category ;
	}
	
	
	
}
