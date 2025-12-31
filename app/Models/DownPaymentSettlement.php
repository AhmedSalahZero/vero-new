<?php

namespace App\Models;

use App\Services\Api\OdooPayment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * * هي عباره عن ال 
 * * down payment  Settlements
 * * الخاصة بال money received
 */
class DownPaymentSettlement extends Model
{
	protected $guarded = ['id'];
	protected $table ='down_payment_settlements';
	
	protected static function booted()
	{
		self::deleting(function (self $downPaymentSettlement): void {
			$moneyReceived = $downPaymentSettlement->moneyReceived;
			$company =$moneyReceived->company;
			if($company->hasOdooIntegrationCredentials()){
				$odooId = $moneyReceived->odoo_id ;
				if($odooId){
					$odooPaymentService = new OdooPayment($company);
					$odooPaymentService->cancelDownPayment($odooId);
				}
			}
		});
		
	}
	
	
	
	public function moneyReceived()
	{
		return $this->belongsTo(MoneyReceived::class , 'money_received_id','id');
	}


	public function getAmount()
	{
		return $this->settlement_amount ;
	}	
	public function getWithhold()
	{
		return $this->withhold_amount ;
	}		

	public function getInvoiceNumber()
	{
		return $this->invoice_number ; 
	}


	public function getSettlementAmount()
	{
		return $this->settlement_amount?:0 ; 
	}
	public function getSettlementAmountFormatted()
	{
		return number_format($this->getSettlementAmount(),0);
	}
	
	public function getSettlementDateFormatted()
    {
        $settlementDate = $this->getSettlementDate() ;
        if($settlementDate) {
            return Carbon::make($settlementDate)->format('d-m-Y');
        }
    }
	
}
