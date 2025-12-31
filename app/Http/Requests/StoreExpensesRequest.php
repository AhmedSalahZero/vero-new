<?php

namespace App\Http\Requests;

use App\Rules\ExpenseStartDateAndEndDateRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpensesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        	// 'fixed_monthly_repeating_amount'=>[ new ExpenseStartDateAndEndDateRule($this->study) ],
        	// 'percentage_of_sales'=>[ new ExpenseStartDateAndEndDateRule($this->study,'monthly_percentage') ],
        	// 'cost_per_unit'=>[ new ExpenseStartDateAndEndDateRule($this->study,'monthly_cost_of_unit') ],
        	// 'one_time_expense'=>[ new ExpenseStartDateAndEndDateRule($this->study) ],
        	// 'expense_per_employee'=>[ new ExpenseStartDateAndEndDateRule($this->study,'monthly_cost_of_unit') ],
        ];
    }
	
}
