<?php

namespace App\Http\Requests\NonBankingServices;

use App\Rules\UniqueToCompanyRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseNamesRequest extends FormRequest
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
            // 'name'=>['required',new UniqueToCompanyRule('Department','name',$this->id,__('This Name Already Exist'),'\App\Models\NonBankingService\\')],
        ];
    }
}
