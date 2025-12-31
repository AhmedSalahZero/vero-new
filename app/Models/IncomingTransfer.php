<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingTransfer extends Model
{
	protected $with = [
		'accountType',
		'receivingBank'
	];
	 
    protected $guarded = ['id'];
	
	public function moneyReceived()
	{
		return $this->belongsTo(MoneyReceived::class,'money_received_id');
	}
	public function receivingBank():?BelongsTo{
		return $this->belongsTo(FinancialInstitution::class,'receiving_bank_id','id');
	}
	
	public function getReceivingBankId()
	{
		$bank = $this->receivingBank;
		return $bank ? $bank->id : 0 ;
	}
	public function getReceivingBankName()
	{
		$bank = $this->receivingBank;
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
	
}
