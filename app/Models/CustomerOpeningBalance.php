<?php

namespace App\Models;

use App\Models\Company;
use App\Models\MoneyReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomerOpeningBalance extends Model
{
    protected $guarded = ['id'];
	const OPEN_BALANCE  = 'opening-balance';
	public function getId()
	{
		return $this->id;
	}
	public function getDate()
	{
		return $this->date; 
	}
	
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}
	public function customerInvoices()
	{
		return $this->hasMany(CustomerInvoice::class,'opening_balance_id','id');
	}
	public function moneyModel()
	{
		return $this->hasMany(MoneyReceived::class,'advanced_opening_balance_id','id');
	}
	public function setDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		
		$this->attributes['date'] = $year.'-'.$month.'-'.$day;
	}


	
	
	
}
