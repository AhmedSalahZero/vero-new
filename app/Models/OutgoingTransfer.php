<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class OutgoingTransfer extends Model
{
	protected $with = [
		'accountType',
		'deliveryBank'
	];
	const PENDING = 'pending';
	const PAID = 'paid';
	
    protected $guarded = ['id'];
	
	public function getStatus()
	{
		return $this->status ;
	}
	
	public function getStatusFormatted()
	{
		return snakeToCamel($this->getStatus());
	}
	
	public function moneyPayment()
	{
		return $this->belongsTo(MoneyPayment::class,'money_payment_id');
	}
	public function cashExpense()
	{
		return $this->belongsTo(CashExpense::class,'cash_expense_id');
	}
	/**
	 * * البنك اللي طلعنا منه التحويلة
	 */
	public function deliveryBank():?BelongsTo{
		return $this->belongsTo(FinancialInstitution::class,'delivery_bank_id','id');
	}
	
	public function getDeliveryBankId()
	{
		$bank = $this->deliveryBank;
		return $bank ? $bank->id : 0 ;
	}
	public function getDeliveryBankName()
	{
		$bank = $this->deliveryBank;
		return $bank ? $bank->getName() : __('N/A') ;
	}
	public function getReceiptNumber()
	{
		return $this->receipt_number ;
	}
	public function accountType()
	{
		return $this->belongsTo(AccountType::class,'account_type','id');
	}
	public function getAccountTypeId()
	{
		$accountType = $this->accountType; 
		return $accountType ? $accountType->id : 0 ; 
	}
	public function getAccountTypeName()
	{
		$accountType = $this->accountType; 
		return $accountType ? $accountType->getName() : __('N/A') ; 
	}
	public function getAccountNumber()
	{
		return $this->account_number;
	}
	public function actualPaymentDate()
	{
		return $this->actual_payment_date ;
	}
	public function actualPaymentDateFormatted()
	{
		$date  = $this->actualPaymentDate() ;
		return $date ? Carbon::make($date)->format('d-m-Y') : null ;
	}
	public function setActualPaymentDateAttribute($value)
	{
		if(!$value){
			return null ;
		}
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['actual_payment_date'] = $value;
			return  ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		$this->attributes['actual_payment_date'] = $year.'-'.$month.'-'.$day;
	}
	public function isBankCharges():bool 
	{
		return (bool) $this->is_bank_charges;
	}	
}
