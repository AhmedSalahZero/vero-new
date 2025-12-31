<?php

namespace App\Models;

use App\Traits\Models\HasCreditStatements;
use App\Traits\Models\HasDeleteButTriggerChangeOnLastElement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LoanScheduleSettlement extends Model
{
	use HasCreditStatements,HasDeleteButTriggerChangeOnLastElement;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */

    protected $guarded = [];



    /**
     * The table associated with the model.
     *
     * @var string
     */
	public static function boot()
	{
		
		parent::boot();
		static::created(function(self $loanScheduleSettlement){
			$loanScheduleSettlement->updateLoanScheduleRemaining();
		});
		static::deleted(function(self $loanScheduleSettlement){
			$loanScheduleSettlement->updateLoanScheduleRemaining();
		});
		static::updated(function(self $loanScheduleSettlement){
			$loanScheduleSettlement->updateLoanScheduleRemaining();
		});
	}
	public function updateLoanScheduleRemaining()
	{
		$loanSchedule = $this->loanSchedule ;
		$totalSettlement = 0 ;
		foreach($loanSchedule->settlements as $settlement){
			$totalSettlement+= $settlement->getAmount();
		}
		$totalLoanScheduleAmount = $loanSchedule->getSchedulePayment();
		$loanSchedule->update([
			'remaining'=>$totalLoanScheduleAmount - $totalSettlement 
		]);
		
	}
	
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id?? Request('company_id') );
    }
	public function getAmount()
	{
		return $this->amount ;
	}
	public function getAmountFormatted()
	{
		return number_format($this->getAmount());
	}
	public function getCurrentAccountNumber()
	{
		return $this->current_account_number ;
	}
	public function getDate()
	{
		return $this->date;
	}
	public function getDateFormatted()
	{
		$date = $this->getDate();
		return  $date ? Carbon::make($date)->format('d-m-Y') : __('N/A') ;
	}
	public function setDateAttribute($value)
	{
		if(is_object($value)){
			return $value ;
		}
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['date'] = $value;
			return  ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		$this->attributes['date'] = $year.'-'.$month.'-'.$day;
		
	}
	public function getAccountNumber()
	{
		return $this->current_account_number ;
	}
	public function loanSchedule()
	{
		return $this->belongsTo(LoanSchedule::class,'loan_schedule_id','id');
	}
	public function currentAccountCreditBankStatement()
	{
		return $this->hasOne(CurrentAccountBankStatement::class,'loan_schedule_settlement_id','id')->where('is_credit',1);
	}
	public function currentAccountCreditBankStatements()
	{
		return $this->hasMany(CurrentAccountBankStatement::class,'loan_schedule_settlement_id','id')->where('is_credit',1)->orderBy('full_date','desc');
	}
	public function loanStatement()
	{
		return $this->hasOne(CurrentAccountBankStatement::class,'loan_schedule_settlement_id','id');
	}
	public function loanStatements()
	{
		return $this->hasMany(LoanStatement::class,'loan_schedule_settlement_id','id')->orderBy('full_date','desc');
	}
	public function deleteAllRelations()
	{
		CurrentAccountBankStatement::deleteButTriggerChangeOnLastElement($this->currentAccountCreditBankStatements);
		LoanStatement::deleteButTriggerChangeOnLastElement($this->loanStatements);
	}
	public function handleLoanStatement(int $companyId , int $financialInstitutionId  , string $accountNumber,string $date , $debitAmount , string $commentEn , string $commentAr)
	{
		$financialInstitutionAccount = FinancialInstitutionAccount::findByAccountNumber($accountNumber,$companyId,$financialInstitutionId);
		$this->loanStatements()->create([
			'financial_institution_account_id'=>$financialInstitutionAccount->id ,
			'company_id'=>$companyId , 
			'is_debit'=>1 ,
			'is_credit'=> 0 ,
			'date'=>$date ,
			'debit'=>$debitAmount,
			'comment_en'=>$commentEn ,
			'comment_ar'=>$commentAr
		]);
	}
}
