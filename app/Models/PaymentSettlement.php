<?php

namespace App\Models;

use App\Services\Api\OdooPayment;
use App\Traits\Models\HasDeleteButTriggerChangeOnLastElement;
use App\Traits\Models\IsSettlement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSettlement extends Model
{
	use HasDeleteButTriggerChangeOnLastElement ,  IsSettlement;
	protected $guarded = ['id'];
	protected static function booted()
	{
		self::deleting(function (self $settlement): void {
			$company =$settlement->company;
			if($company->hasOdooIntegrationCredentials()){
				if($settlement->odoo_id){
					$odooPaymentService = new OdooPayment($company);
					$odooPaymentService->cancelPayments($settlement->odoo_id);
				}
			}
		});
		
	}
	public function moneyPayment()
	{
		return $this->belongsTo(MoneyReceived::class , 'money_payment_id','id');
	}
	
	public function supplierInvoice()
	{
		return $this->belongsTo(SupplierInvoice::class , 'invoice_id','id');
	}
	public function invoice():BelongsTo
	{
		return $this->supplierInvoice();
	}

	public function letterOfCreditIssuance()
	{
		return $this->belongsTo(LetterOfCreditIssuance::class ,'letter_of_credit_issuance_id');
	}
	
}
