<?php

namespace App\Http\Requests;

use App\Models\NonBankingService\Study;
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
		$study = $this->route('study');
		$request = Request();
		/**
		 * @var Study $study 
		 */
		$generalFixedAssets = $study->prepareGeneralFixedAssetToBeStored($request->get('ffe',[]));
		$perEmployeeFixedAssets = $study->prepareFixedAssetPerEmployeesToBeStored($request->get('per-employee',[]));
		$newBranchFixedAssets = $study->prepareNewBranchFixedAssetToBeStored($request->get('new-branch',[]));
		$fixedAssets =array_merge($generalFixedAssets,$perEmployeeFixedAssets,$newBranchFixedAssets);
        
        $this->merge([
            'fixedAssets'=>$fixedAssets
        ]);
		return [];
    
    }
}
