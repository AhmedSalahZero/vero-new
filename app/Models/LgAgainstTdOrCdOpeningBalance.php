<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class LgAgainstTdOrCdOpeningBalance extends Model
{
    protected $guarded = ['id'];
    protected $table = 'lg_against_td_or_cd_opening_balances';
	public function getId()
	{
		return $this->id;
	}
    public function getLgType()
    {
        return $this->lg_type ;
    }
    /**
     * * رقم الحساب الجاري
     */
    public function getAccountNumber()
    {
        return $this->account_number ;
    }
    public function getCurrency(){
        return $this->currency;
    }
    public function getAccountType(){
        return $this->account_type;
    }
    public function getAmount()
    {
        return $this->amount ?:0;
    }
    public function getAmountFormatted()
    {
        return number_format($this->getAmount());
    }
	public function getLgEndDate()
	{
		return $this->lg_end_date;
	}
    public function getEndDate()
	{
		return $this->lg_end_date;
	}
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}

	// public function lgOpeningBalance()
	// {
	// 	return $this->hasMany(LgOpeningBalance::class , 'lg_opening_balance_id','id');
	// }
	public function setLgEndDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['lg_end_date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		$this->attributes['lg_end_date'] = $year.'-'.$month.'-'.$day;
	}





}
