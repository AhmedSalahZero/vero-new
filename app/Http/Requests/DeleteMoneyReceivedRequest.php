<?php

namespace App\Http\Requests;

use App\Models\MoneyReceived;
use App\Rules\MoneyReceivedCanBeDeletedRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteMoneyReceivedRequest extends FormRequest
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
		$moneyReceived = Request()->route('moneyReceived') ;
		/**
		 * @var MoneyReceived $moneyReceived 
		 */
		$company = Request()->route('company');
	
        return [
            'net_balance'=>[new MoneyReceivedCanBeDeletedRule($moneyReceived ,$company )]
        ];
    }
}
