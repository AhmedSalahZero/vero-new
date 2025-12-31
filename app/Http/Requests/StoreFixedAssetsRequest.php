<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreFixedAssetsRequest extends FormRequest
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
            'fixedAssets.*.'
        ];
    }
    protected function prepareForValidation()
    {
        $fixedAssets = Request()->get('fixedAssets', []);
        foreach ($fixedAssets as $index => &$fixedAssetArr) {
     
            $fixedAssetArr['ffe_item_cost'] = number_unformat($fixedAssetArr['ffe_item_cost']);
         
			$fixedAssetArr['due_days'] = array_unique($fixedAssetArr['due_days']??[]);
			
            foreach ($fixedAssetArr['due_days']??[] as $index => $dueDay) {
                $paymentRate = $fixedAssetArr['payment_rate'][$index];
                $fixedAssetArr['custom_collection_policy'][$dueDay] = isset($fixedAssetArr['custom_collection_policy'][$dueDay]) ? $fixedAssetArr['custom_collection_policy'][$dueDay]+ $paymentRate :$paymentRate;
            }
		
		
        }
        $this->merge([
            'fixedAssets'=>$fixedAssets
        ]);
    
    }
}
