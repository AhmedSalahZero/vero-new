<?php

namespace App\Http\Requests;

use App\Rules\DateMustBeGreaterThanOrEqualDate;
use App\Rules\NumberMustBeGreaterThanOrEqualRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDownPaymentSettlementRequest extends FormRequest
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
		$modelType = Request('model_type');
		$downPaymentId = Request('down_payment_id');
		// SECURITY: model type allow-listed and the down payment scoped to the route company.
		$fullClassName = \App\Support\CompanyScopedModelResolver::invoiceClass($modelType);
		$downPaymentModelName=$fullClassName::MONEY_MODEL_NAME;
		$downPaymentModelFullName = 'App\Models\\'.$downPaymentModelName ;   
		$downPayment =$downPaymentModelFullName::where('company_id', (int) \App\Support\CompanyScopedModelResolver::routeCompanyId($this))->findOrFail($downPaymentId);
		$receivingDate  = $downPayment->getDate();
		$settlementDate = Request('settlement_date');
		$greaterNumber = Request()->get('received_amount',0);
		$settlementAmount = array_sum(array_column(Request('settlements',[]),'settlement_amount'));
		$message = __('Total Settlements Must Be Equal Or Less Than Down Payment Amounts');
		
        return [
            'settlement_date'=> ['required',new DateMustBeGreaterThanOrEqualDate($settlementDate,$receivingDate,__('Settlement Date Must Be Greater Or Equal Down Payment Receiving Date'))],
			'received_amount'=>['required',new NumberMustBeGreaterThanOrEqualRule($greaterNumber,$settlementAmount,$message)]
        ];
    }
}
