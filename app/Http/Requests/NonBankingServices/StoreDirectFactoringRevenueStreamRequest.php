<?php

namespace App\Http\Requests\NonBankingServices;

use App\Rules\DirectFactoringBreakdownRule;
use App\Rules\TotalBreakdownMustBeHundredRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDirectFactoringRevenueStreamRequest extends FormRequest
{
	protected string $errorMessage ;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

  
    public function rules()
    {
		

        return [
            'total_must_be_hundred'=>[new TotalBreakdownMustBeHundredRule('directFactoringBreakdowns')],
			'direct_factoring_breakdown_rules'=>[new DirectFactoringBreakdownRule],
        ];
    }
}
