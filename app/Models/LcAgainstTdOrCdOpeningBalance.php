<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class LcAgainstTdOrCdOpeningBalance extends Model
{
    protected $guarded = ['id'];
    protected $table = 'lc_against_td_or_cd_opening_balances';
	public function getId()
	{
		return $this->id;
	}
    public function getLcType()
    {
        return $this->lc_type ;
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
	public function getLcEndDate()
	{
		return $this->lc_end_date;
	}
    public function getEndDate()
	{
		return $this->lc_end_date;
	}
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}

	// public function lcOpeningBalance()
	// {
	// 	return $this->hasMany(LcOpeningBalance::class , 'lc_opening_balance_id','id');
	// }
	public function setLcEndDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['lc_end_date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		$this->attributes['lc_end_date'] = $year.'-'.$month.'-'.$day;
	}





}
