<?php

namespace App\Http\Requests\NonBankingServices;

use App\Rules\LeasingBreakdownRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeasingRevenueStreamRequest extends FormRequest
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

		$items = [];
		foreach($this->input('leasingRevenueStreamBreakdown.sub_items',[]) as $index=>$item){
			$item['step_up'] =isset($item['step_rate']) && $item['step_rate'] >= 0? $item['step_rate'] : 0 ;
			$item['step_down'] = isset($item['step_rate']) && $item['step_rate'] < 0 ? $item['step_rate'] : 0 ;
			unset($item['step_rate']);
			$items[$index]=$item;
		}
		
		$this->merge([
			'leasingRevenueStreamBreakdown'=>[
				'sub_items'=>$items
			]
		]);
	}
    public function rules()
    {
	
        return [
        //    'category'=>[new LeasingBreakdownRule]
        ];
    }
}
