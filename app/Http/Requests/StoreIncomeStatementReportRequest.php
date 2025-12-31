<?php

namespace App\Http\Requests;

use App\Rules\MustBeUniqueToIncomeStatement;
use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeStatementReportRequest extends FormRequest
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
			
				'sub_items.*.name' =>['required',new MustBeUniqueToIncomeStatement(getCurrentCompanyId(),Request()->input('financial_statement_able_id'),)],
				'sub_items.*.collection_policy'=>'sometimes|required|array',
				'sub_items.*.collection_policy.type'=>'sometimes|required|array',
				'sub_items.*.collection_policy.type.name'=>'sometimes|required'

        ];
    }
	public function messages()
	{
		return  [
					'sub_items.*.name.required' => __('Please Enter SubItem Name'),
					'sub_items.*.collection_policy.required'=>__('Please Enter Collection / Payment Policy'),
					'sub_items.*.collection_policy.type.required'=>__('Please Enter Collection / Payment Policy'),
					'sub_items.*.collection_policy.type.name.required'=>__('Please Enter Collection / Payment Policy'),
				];
	}
}
