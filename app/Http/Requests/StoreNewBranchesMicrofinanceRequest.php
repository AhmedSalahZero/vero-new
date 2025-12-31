<?php

namespace App\Http\Requests;

use App\Rules\FlatRateRule;
use App\Rules\ProductMixRule;
use App\Rules\ProductSeasonalityRule;
use App\Rules\StartDateAndOperationDateRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewBranchesMicrofinanceRequest extends FormRequest
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
            'newBranchMicrofinanceOpeningProjections'=>['required',new StartDateAndOperationDateRule($this->study)],
            'microfinanceProductSalesProjects'=>['required',new ProductMixRule($this->study) , new ProductSeasonalityRule($this->study) , new FlatRateRule($this->study)],
        ];
    }
}
