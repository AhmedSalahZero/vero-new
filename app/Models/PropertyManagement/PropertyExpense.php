<?php

namespace App\Models\PropertyManagement;

use App\Helpers\HStr;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @mixin IdeHelperPropertyExpense
 */
class PropertyExpense extends Model
{
    use BelongsToStudy,BelongsToCompany;
    protected $guarded = ['id'];
    protected $connection ='property_management';
    protected $casts = [
		
    ];
        public static function boot()
        {
            parent::boot();
            static::saving(function($row){
            });
        }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function getExpenseNameId()
    {
        return $this->expense_name_id ;
    }
	public function getExpenseName()
	{
		return $this->expenseName->name;
	}
	public function expenseName()
	{
		return $this->belongsTo(ExpenseName::class, 'expense_name_id', 'id');
	}
	
	public function getExpenseCategoryName():string 
	{
		return ExpenseName::getCategories(app(Company::class))[$this->expense_category]??'';
	}
    public function getDateAsString():string
    {
        return $this->date;
    }
	public function getDateFormattedForVueDatePicker():array
    {
        return formatDateForVueDatePicker($this->date);
    }
	public function setDateAttribute($value)
	{
		$this->attributes['date'] = $value ? Carbon::make($value)->format('Y-m-d') : null;
	}
	public function getPaymentDateAsString():?string 
	{
		return $this->payment_date;
	}
	public function getPaymentDateFormattedForVueDatePicker():?array
	{
		return $this->payment_date ? formatDateForVueDatePicker($this->payment_date) : [];
	}
	public function setPaymentDateAttribute($value)
	{
		$this->attributes['payment_date'] = $value ? Carbon::make($value)->format('Y-m-d') : null;
		// $this->attributes['payment_date'] = $value ? formatDateFromMonthPicker($value) : null;
	}
    public function getAmount()
    {
        return $this->amount ?: 0 ;
    }
	public function isPaid():bool
	{
		return $this->is_paid;
	}
	
	public function getNote():?string
	{
		return $this->note;
	}
	public static function generateRow($expense,Property $property,array $expenseNamesPerCategories )
	{
	
		return [
				  'id'=>$expense ? $expense->id : 0,
                    'expense_category'=>$expense ? $expense->expense_category : '',
                    'expense_name_id'=>$expense ? $expense->expense_name_id : '',
					'filteredExpenseNamesOptions'=>$expense ? ($expenseNamesPerCategories[$expense->expense_category]??[])  : [],
					'amount'=>$expense? $expense->amount:0,//null is important as default // for monthly repeating and one time expense only
					'date'=>$expense ? $expense->date : now()->format('Y-m-d'),
					'payment_date'=>$expense ? $expense->payment_date : now()->format('Y-m-d'),
					'is_paid'=>$expense ? $expense->isPaid() : false,
					'note'=>$expense ? $expense->note : '',
					'property_id'=> $property->id ,
					'company_id'=> $property->company_id ,
			];
	
	}
}
