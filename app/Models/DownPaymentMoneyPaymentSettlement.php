<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * * هي عباره عن ال 
 * * down payment  Settlements
 * * الخاصة بال money Payment
 */
class DownPaymentMoneyPaymentSettlement extends Model
{
	protected $guarded = ['id'];
	protected $table ='down_payment_money_payment_settlements';
	// protected $table ='down_payment_payment_settlements';
	public function moneyPayment()
	{
		return $this->belongsTo(MoneyPayment::class , 'money_payment_id','id');
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
