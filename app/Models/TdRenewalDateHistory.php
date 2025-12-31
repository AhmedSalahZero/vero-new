<?php

namespace App\Models;

use App\Traits\Models\HasDeleteButTriggerChangeOnLastElement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TdRenewalDateHistory extends Model
{
	use HasDeleteButTriggerChangeOnLastElement;

	protected $guarded = [
		'id'
	];
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}

	
	public function getExpiryDate()
    {
        return $this->expiry_date ;
    }
	public function getExpiryDateFormatted()
    {
		$expiryDate = $this->getExpiryDate() ;
        return $expiryDate ? Carbon::make($expiryDate)->format('d-m-Y') : null   ;
    }
	public function setExpiryDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['expiry_date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		
		$this->attributes['expiry_date'] = $year.'-'.$month.'-'.$day;
	}
	
	public function getRenewalDate()
    {
        return $this->renewal_date ;
    }
	public function getRenewalDateFormatted()
    {
		$renewalDate = $this->getRenewalDate() ;
        return $renewalDate ? Carbon::make($renewalDate)->format('d-m-Y') : null   ;
    }
	public function setRenewalDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['renewal_date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		
		$this->attributes['renewal_date'] = $year.'-'.$month.'-'.$day;
	}
	public function getRenewalDateFormattedForDatePicker()
	{
		$date = $this->getRenewalDate();
		return $date ? Carbon::make($date)->format('m/d/Y') : null;
	}
	public function getDuration()
	{
		return Carbon::make($this->getRenewalDate())->diffInDays($this->getExpiryDate());
	}
	public function getInterestRate()
	{
		return $this->interest_rate ?: 0 ;
	}
	public function getInterestRateFormatted()
	{
		return number_format($this->getInterestRate())  ;
	}
	// public function getFeesAmount()
	// {
	// 	return $this->fees_amount ;
	// }
	// public function getFeesAmountFormatted()
	// {
	// 	$amount = $this->getFeesAmount();
	// 	return number_format($amount) ;
	// }
	// public function commissionCurrentBankStatements():HasMany
	// {
	// 	return $this->hasMany(CurrentAccountBankStatement::class,'lg_renewal_date_history_id','id');
	// }
	
}
