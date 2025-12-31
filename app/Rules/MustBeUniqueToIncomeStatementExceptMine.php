<?php

namespace App\Rules;

use App\Models\IncomeStatement;
use Illuminate\Contracts\Validation\Rule;

class MustBeUniqueToIncomeStatementExceptMine implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
	protected $company_id ; 
	protected $income_statement_id ;
	protected $sub_item_type ;
	protected $id ;
    public function __construct(int $companyId  , int $incomeStatementId,string $subItemType,int $id)
    {
        $this->company_id = $companyId;
        $this->income_statement_id = $incomeStatementId;
        $this->sub_item_type = $subItemType;
        $this->id =$id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
       $incomeStatement = IncomeStatement::find($this->income_statement_id);
	   
	   return !in_array($value,$incomeStatement->subItems()->wherePivot('sub_item_type',$this->sub_item_type)->wherePivot('id','!=',$this->id)->get()->pluck('pivot.sub_item_name')->unique()->toArray());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This Name Already Exist ';
    }
	

}
