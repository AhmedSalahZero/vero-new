<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LendingInformationAgainstAssignmentOfContract extends Model
{
    protected $guarded = ['id'];
	
	public static function boot()
	{
		parent::boot();
		static::updated(function(self $lendingInformationAgainstAssignmentOfContract){
			// $lendingInformationAgainstAssignmentOfContract->overdraftAgainstAssignmentOfContract->triggerChangeOnContracts();
		});
		static::deleting(function(self $lendingInformationAgainstAssignmentOfContract){
			$lendingInformationAgainstAssignmentOfContract->contract->update([
				'overdraft_against_assignment_of_contract_id'=>null ,
				'updated_at'=>now()
			]);
		});
		
		static::deleted(function(self $lendingInformationAgainstAssignmentOfContract){
			$lendingInformationAgainstAssignmentOfContract->overdraftAgainstAssignmentOfContract->triggerChangeOnContracts();
		});
	}
	public function overdraftAgainstAssignmentOfContract()
	{
		return $this->belongsTo(OverdraftAgainstAssignmentOfContract::class,'overdraft_against_assignment_of_contract_id','id');
	}
	
	public function getId(){
		return $this->id ; 
	}
	public function getCustomerId()
	{
		return $this->customer_id;
	}	
	public function customer()
	{
		return $this->belongsTo(Partner::class,'customer_id','id');
	}
	public function getCustomerName()
	{
		return $this->customer ? $this->customer->getName():__('N/A');
	}
	
	public function contract()
	{
		return $this->belongsTo(Contract::class,'contract_id','id');
	}
	public function getContractStartDate()
	{
		return $this->contract ? $this->contract->getStartDate():__('N/A');
		
	}
	public function getContractEndDate()
	{
		return $this->contract ? $this->contract->getEndDate():__('N/A');
	}
	public function getAssignmentEndDate()
	{
		return $this->assignment_date;
	}
	public function getContractAmount()
	{
		return $this->contract ? $this->contract->getAmount():0;
	}
	public function getContractAmountFormatted()
	{
		return number_format($this->getContractAmount());
	}
	public function getLendingAmount()
	{
		return $this->getLendingRate() / 100 * $this->getContractAmount();
	}
	public function getLendingAmountFormatted():string 
	{
		return number_format($this->getLendingAmount());
	}
	public function getContractName()
	{
		return $this->contract ? $this->contract->getName():__('N/A');
	}
	public function getLendingRate()
	{
		return $this->lending_rate ?: 0 ;
	}
	public function getLendingRateFormatted()
	{
		return number_format($this->getLendingRate()) ;
	}
	
}
