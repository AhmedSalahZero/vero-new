<?php

namespace App\Http\Requests;

use App\Http\Controllers\MoneyReceivedController;
use App\Rules\MoneyReceivedCanBackToUnderCollectionRule;
use Illuminate\Foundation\Http\FormRequest;

class BackToUnderCollectionChequeRequest extends FormRequest
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
			'amount'=>[new MoneyReceivedCanBackToUnderCollectionRule($this->company,$this->moneyReceived)] 
		] ;
    }
	
}
