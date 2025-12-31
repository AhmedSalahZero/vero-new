<?php

namespace App\Http\Requests\NonBankingServices;

use App\Models\FinancialPlanning\Position;
use App\Models\NonBankingService\Study;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewBranchFixedAssetsRequest extends FormRequest
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
	
		$fixedAssets = $this->get('fixedAssets');
		$fixedAssetType = $this->get('fixed_asset_type');
		foreach($fixedAssets as $index => &$fixedAssetArr){
			$fixedAssetArr['ffe_counts'] =$fixedAssetArr['ffe_counts'] ? (array)json_decode($fixedAssetArr['ffe_counts']) : [];
			$fixedAssetArr['type'] =$fixedAssetType;
			
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
    public function rules()
    {
        return [
            //
        ];
    }
}
