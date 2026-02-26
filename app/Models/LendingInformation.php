<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LendingInformation extends Model
{
    protected $guarded = ['id'];
	public function overdraftAgainstCommercialPaper()
	{
		return $this->belongsTo(OverdraftAgainstCommercialPaper::class,'overdraft_against_commercial_paper_id','id');
	}
	public function getId(){
		return $this->id ; 
	}
	public function getCustomerId()
	{
		return $this->customer_id;
	}	
	public function getAccountNumber()
	{
		return $this->account_number ;
	}
	public function getToBeSetteledMaxWithinDays()
	{
		return $this->to_be_setteled_max_within_days?:0;
	}
	public function getMaxLendingLimitPerCustomer()
	{
		return $this->max_lending_limit_per_customer?:0;
	}
}
