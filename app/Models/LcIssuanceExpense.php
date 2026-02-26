<?php

namespace App\Models;


use App\Traits\Models\HasLetterOfGuaranteeCashCoverStatements;
use App\Traits\Models\HasLetterOfGuaranteeStatements;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LcIssuanceExpense extends Model
{
	use HasLetterOfGuaranteeStatements,HasLetterOfGuaranteeCashCoverStatements;
	protected $table ='lc_issuance_expenses';
	protected $guarded =  [
		'id'
	];
	protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
			$model->deleteAllRelations();
        });
    }	
	
	public function letterOfCreditIssuance()
	{
		return $this->belongsTo(LetterOfCreditIssuance::class,'lc_issuance_id');
	}
	public function getDate()
    {
        return $this->date ;
    }
	public function getDateFormatted()
    {
		$date = $this->getDate() ;
        return $date ? Carbon::make($date)->format('d-m-Y') : null   ;
    }
	public function getAmount()
	{
		return $this->amount ?:0 ;
	}
	public function getAmountFormatted()
	{
		return number_format($this->getAmount()) ;
	}	
	public function getName()
	{
		return $this->expense_name ;
	}
	public function currentAccountBankStatements():HasMany
	{
		return $this->hasMany(CurrentAccountBankStatement::class,'lc_issuance_expense_id','id');
	}	
	public function currentAccountCreditBankStatement()
	{
		return $this->hasOne(CurrentAccountBankStatement::class,'lc_issuance_expense_id','id')->where('is_credit',1);
	}
	public function currentAccountCreditBankStatements()
	{
		return $this->hasMany(CurrentAccountBankStatement::class,'lc_issuance_expense_id','id')->where('is_credit',1)->orderBy('full_date','desc');
	}
	public function deleteAllRelations()
	{
		CurrentAccountBankStatement::deleteButTriggerChangeOnLastElement($this->currentAccountCreditBankStatements);
		
	}
	public function getCurrency()
	{
		return $this->currency;
	}
	public function getExchangeRate()
	{
		return $this->exchange_rate ?: 1 ;
	}
	
}
