<?php

namespace App\Http\Requests\NonBankingServices;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioMortgageRevenueStreamRequest extends FormRequest
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
		// $items = [];
		// foreach($this->get('leasingRevenueStreamBreakdown',[]) as $index=>$item){
		// 	$item['company_id']= $this->get('company_id');
		// 	$item['study_id']= $this->get('study_id');
		// 	$item['step_up'] =$item['step_rate'] >= 0? $item['step_rate'] : 0 ;
		// 	$item['step_down'] = $item['step_rate'] < 0 ? $item['step_rate'] : 0 ;
		// 	unset($item['step_rate']);
		// 	$items[$index]=$item;
		// }
		
		// $this->merge([
		// 	'leasingRevenueStreamBreakdown'=>$items 
		// ]);
	}
    public function rules()
    {
        return [
            //
        ];
    }
}
