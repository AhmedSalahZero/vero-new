<?php

namespace App\Http\Requests;

use App\Rules\LcTermAmountRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLetterOfCreditIssuanceRequest extends FormRequest
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
			'lc_amount'=>['required','gt:0'],
			// 'cash_cover_amount'=>[new LcTermAmountRule($this->get('category_name'),$this->get('cash_cover_deducted_from_account_type'),$this->get('lc_fees_and_commission_account_id'),$this->get('issuance_date'),$this->get('cash_cover_amount',0),$this->get('lc_commission_amount',0),$this->get('min_lc_commission_fees',0),$this->get('issuance_fees',0),$this->get('company_id'),$this->get('financial_institution_id'))]
        ];
    }
}
