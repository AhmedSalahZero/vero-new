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


	public function prepareForValidation():array 
	{

		$items = [];
		foreach($this->input('leasingRevenueStreamBreakdown.sub_items',[]) as $index=>$item){
			$item['step_up'] =isset($item['step_rate']) && $item['step_rate'] >= 0? $item['step_rate'] : 0 ;
			$item['step_down'] = isset($item['step_rate']) && $item['step_rate'] < 0 ? $item['step_rate'] : 0 ;
			unset($item['step_rate']);
			// only the grace loan types have a grace period: a normal or step loan cannot (phase 4). The page disables
			// the field for them but still sends what was typed before the type changed.
			if (!str_contains((string) ($item['loan_type'] ?? 'normal'), 'grace')) {
				$item['grace_period'] = 0;
			}
			$items[$index]=$item;
		}
		
		$this->merge([
			'leasingRevenueStreamBreakdown'=>[
				'sub_items'=>$items
			]
		]);
		return [];
	}
    public function rules()
    {
	
        return [
        //    'category'=>[new LeasingBreakdownRule]
        ];
    }
}
