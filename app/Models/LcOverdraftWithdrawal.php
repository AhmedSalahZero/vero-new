<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class LcOverdraftWithdrawal extends Model
{

	
	protected $guarded =[
		'id'
	];
	public function bankStatement()
	{
		return $this->belongsTo(CleanOverdraftBankStatement::class,'clean_overdraft_bank_statement_id','id');
	}
	public function lcIssuance()
	{
		return $this->belongsTo(LetterOfCreditIssuance::class,'lc_issuance_id','id');
	}
	public function getId()
	{
		return $this->id ;
	}
	public function setWithdrawalDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['withdrawal_date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		
		$this->attributes['withdrawal_date'] = $year.'-'.$month.'-'.$day;
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
	
	
	
	
	
}
