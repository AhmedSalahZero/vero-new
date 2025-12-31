<?php

namespace App\Http\Requests;

use App\Rules\ExpenseStartDateAndEndDateRule;
use App\Rules\ProductMixRule;
use App\Rules\ProductSeasonalityRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreConsumerfinanceRequest extends FormRequest
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
            // 'microfinanceProductSalesProjects'=>['required',new ProductMixRule($this->study)],
            // 'microfinanceProductSalesProjects'=>['required',new ProductSeasonalityRule($this->study)],
			// 'fixed_monthly_repeating_amount'=>['required', new ExpenseStartDateAndEndDateRule($this->study) ]
        ];
    }
}
