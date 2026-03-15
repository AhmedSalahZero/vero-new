<?php
namespace App\Http\Requests\PropertyManagements;


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

	
    public function rules()
    {
        return [
            // 'name'=>['required',new UniqueToCompanyRule('Department','name',$this->id,__('This Name Already Exist'),'\App\Models\NonBankingService\\')],
        ];
    }
}
