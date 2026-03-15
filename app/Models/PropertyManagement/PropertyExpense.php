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
 * @property int $id
 * @property int $property_id
 * @property int $expense_name_id
 * @property string $expense_category
 * @property int $company_id
 * @property string $date
 * @property numeric $amount
 * @property int $is_paid
 * @property string|null $payment_date
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\ExpenseName|null $expenseName
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereExpenseCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereExpenseNameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyExpense whereUpdatedAt($value)
 * @mixin \Eloquent
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
		return  (bool)$this->is_paid;
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
