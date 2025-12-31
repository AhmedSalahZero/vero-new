<?php

namespace App\Http\Requests;

use App\Rules\UniqueToCompanyAndAdditionalColumnsRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLetterOfCreditFacilityRequest extends FormRequest
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
			'name'=>['required',new UniqueToCompanyAndAdditionalColumnsRule('LetterOfCreditFacility','name',$this->id,[['financial_institution_id','=',$this->financial_institution_id]],__('This Letter OF Credit Facility Already Exist'))]
        ];
    }
}
