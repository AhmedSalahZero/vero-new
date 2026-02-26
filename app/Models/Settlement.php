<?php

namespace App\Models;

use App\Services\Api\OdooPayment;
use App\Traits\Models\IsSettlement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
	use IsSettlement;
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
	public function moneyReceived()
	{
		return $this->belongsTo(MoneyReceived::class , 'money_received_id','id');
	}
	
	public function customerInvoice()
	{
		return $this->belongsTo(CustomerInvoice::class , 'invoice_id','id');
	}
	public function invoice():BelongsTo
	{
		return $this->customerInvoice();
	}
	
	
}
