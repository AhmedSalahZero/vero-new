<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class SupplierOpeningBalance extends Model
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
	public function supplierInvoices()
	{
		return $this->hasMany(SupplierInvoice::class,'opening_balance_id','id');
		
	}
	public function moneyModel()
	{
		return $this->hasMany(MoneyPayment::class,'advanced_opening_balance_id','id');
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
