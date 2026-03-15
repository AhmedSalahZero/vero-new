<?php

namespace App\Http\Requests;

use App\Models\PropertyManagement\Study;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreFixedAssetsForPropertyRequest extends FormRequest
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

    public function rules()
    {
        return [
            'fixedAssets.*.'
        ];
    }
    protected function prepareForValidation():array 
    {
		$study = $this->route('study');
		$request = Request();
		/**
		 * @var Study $study 
		 */
		$generalFixedAssets = $study->prepareGeneralFixedAssetToBeStored($request->get('ffe',[]));
		$perEmployeeFixedAssets = $study->prepareFixedAssetPerEmployeesToBeStored($request->get('per-employee',[]));
		// $newBranchFixedAssets = $study->prepareNewBranchFixedAssetToBeStored($request->get('new-branch',[]));
		$fixedAssets =array_merge($generalFixedAssets,$perEmployeeFixedAssets);
        
        $this->merge([
            'fixedAssets'=>$fixedAssets
        ]);
		return [];
    
    }
}
