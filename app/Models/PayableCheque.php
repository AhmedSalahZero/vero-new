<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * * دا الشيك اللي بدفعه للموردين
 */

class PayableCheque extends Model
{
	protected $with = [
		'deliveryBank',
		'accountType',
		'moneyPayment',
		'cashExpenses',
		'financialInstitution.bank'
	];
	const PENDING = 'pending';
	const PAID = 'paid';
		 
    protected $guarded = ['id'];
	public function moneyPayment()
	{
		return $this->belongsTo(MoneyPayment::class,'money_payment_id');
	}
	public function cashExpenses()
	{
		return $this->belongsTo(CashExpense::class,'cash_expense_id');
	}
	public static function getChequeTypesForAging():array
	{
		return [
			self::PENDING,
		];
	}
	public function isPending():bool
	{
		return $this->getStatus() == self::PENDING;
	}
	public function isPaid():bool
	{
		return $this->getStatus() == self::PAID;
	}
	public function getDeliveryDate()
	{
		return $this->delivery_date ; 
	}
	public function getDeliveryDateFormatted()
	{
		$deliveryDate = $this->getDeliveryDate();
		return $deliveryDate ? Carbon::make($deliveryDate)->format('d-m-Y'): null ;
	}
	public function setDeliveryDateAttribute($value)
	{
		if(!$value){
			return null ;
		}
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['delivery_date'] = $value;
			return  ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		$this->attributes['delivery_date'] = $year.'-'.$month.'-'.$day;
		
	}
	/**
	 * * هو البنك اللي انا باخد الشيك واسحبة منة وبالتالي لازم يكون من بنوكي
	 */
	public function deliveryBank()
	{
		return $this->belongsTo(FinancialInstitution::class , 'delivery_bank_id','id');
	}
	public function getDeliveryBankId()
	{
		$bank = $this->deliveryBank ;
		return $bank  ? $bank->id : 0 ;
	}
	
	public function getDeliveryBankName()
	{
		$deliveryBank = $this->deliveryBank ;
		return $deliveryBank  ? $deliveryBank->getName() :__('N/A') ;
	}
	public function getChequeNumber()
	{
		return $this->cheque_number ;
	}
	public function getNumber()
	{
		return $this->getChequeNumber();
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
	
	public function getStatus()
	{
		return $this->status ;
	}
	
	public function getStatusFormatted()
	{
		return snakeToCamel($this->getStatus());
	}
	public function getDueDate()
	{
		return $this->due_date;
	}
	public function getDueDateFormatted()
	{
		$dueDate = $this->getDueDate();
		return  $dueDate ? Carbon::make($dueDate)->format('d-m-Y') : null ;
	}
	public function setDueDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['due_date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		
		$this->attributes['due_date'] = $year.'-'.$month.'-'.$day;
	}
	
	/**
	 * * هنعرفه ان كان مستحق الدفع ولا لا كا استرنج مش بولين
	 */
	public function getDueStatus():bool 
	{
		$dueDate = $this->getDueDate();
		return !Carbon::make($dueDate)->greaterThan(now());
	}
	/**
	 * * هنعرفه ان كان مستحق الدفع ولا لا كا استرنج مش بولين
	 */
	public function getDueStatusFormatted():array 
	{
		if($this->getDueStatus()){
			return [
				'status'=>__('Due') ,
				'color'=>'red'
			];
		}
		return [
			'status'=>__('Not Due Yet'),
			'color'=>'green'
		];
		
	}
	
	
		/**
	 * * هو عباره عن رقم الحساب اللي انا سحبت منة الشيك علشان ادية للمورد
	 */
	public function getAccountNumber()
	{
		return $this->account_number;
	}
	public function getAccountBalance()
	{
		return $this->account_balance ;
	}

	
	public function chequeAccountBalance()
	{
		return $this->account_balance?:0 ;
	}
	

	public function chequeActualPaymentDate()
	{
		return $this->actual_payment_date ;
	}
	public function chequeActualPaymentDateFormatted()
	{
		$date  = $this->chequeActualPaymentDate() ;
		return $date ? Carbon::make($date)->format('d-m-Y') : null ;
	}
	public function accountType()
	{
		return $this->belongsTo(AccountType::class,'account_type','id');
	}
	public function getAccountType()
	{
		return $this->account_type ;
	}
	public function getDueAfterDays()
	{
		$secondDate = null ;
		if($this->moneyPayment){
			$secondDate = $this->moneyPayment->getDeliveryDate() ;
		}
		if($this->cashExpense){
			$secondDate = $this->cashExpense->getPaymentDate() ;	
		}
		if(is_null($secondDate)){
			return '-';
		}
		
		$firstDate = Carbon::make($this->getDueDate());
		$secondDate = Carbon::make($secondDate);
		return getDiffBetweenTwoDatesInDays($firstDate , $secondDate);
	}
	public function getPaymentBankName()
	{

		return $this->financialInstitution->bank->getViewName();
	}
	public function financialInstitution()
	{
		return $this->belongsTo(FinancialInstitution::class , 'delivery_bank_id','id');
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



}
