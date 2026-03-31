<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $expense_type
 * @property int|null $company_id
 * @property int|null $created_by
 * @property int $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense whereExpenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingExpense whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class PricingExpense extends Model
{
	protected $guarded = [
		'id'
	];
    
	public function getName()
	{
		return $this->name ;
	}
	public function getExpenseName()
	{
		return $this->getName();
	}
	public static function allFormattedForSelect($type,$companyId)
    {
		$expenses = PricingExpense::where('expense_type',$type)->where('company_id',$companyId)->get();
        return formatOptionsForSelect($expenses , 'getExpenseName' , 'getExpenseName');
    }
	
	public static function getTypes():array 
	{
		return [
			'other-direct-manpower-expense'=>__('Other Direct Manpower Expense'),
			'other-direct-operations-expense'=>__('Other Direct Operations Expense'),
			'sales-and-market-expense'=>__('Sales And Market Expense'),
			'general-and-administrative-expense'=>__('General & Administrative Expense')
		];
	}
}
