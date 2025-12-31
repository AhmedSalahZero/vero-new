<?php

namespace App\Http\Requests\NonBankingServices;

use App\Rules\TotalBreakdownMustBeHundredRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIjaraMortgageRevenueStreamRequest extends FormRequest
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
	public function prepareForValidation()
	{
		
	}
    public function rules()
    {
          return [
            'total_must_be_hundred'=>[new TotalBreakdownMustBeHundredRule('ijaraMortgageBreakdowns')]
        ];
    }
}
