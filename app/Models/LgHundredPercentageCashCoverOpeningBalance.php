<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class LgHundredPercentageCashCoverOpeningBalance extends Model
{
    protected $guarded = ['id'];
    protected $table = 'lg_hundred_percentage_cash_cover_opening_balances';
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
    public function getCurrentAccountNumber()
    {
        return $this->current_account_number ;
    }
    public function getAmount()
    {
        return $this->amount ?:0;
    }
    public function getAmountFormatted()
    {
        return number_format($this->getAmount());
    }
	public function getLgExpiryDate()
	{
		return $this->lg_expiry_date;
	}
    public function getExpiryDate()
	{
		return $this->lg_expiry_date;
	}
    public function getCurrency(){
        return $this->currency;
    }
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}

	// public function lgOpeningBalance()
	// {
	// 	return $this->hasMany(LgOpeningBalance::class , 'lg_opening_balance_id','id');
	// }
	public function setLgExpiryDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['lg_expiry_date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];

		$this->attributes['lg_expiry_date'] = $year.'-'.$month.'-'.$day;
	}





}
