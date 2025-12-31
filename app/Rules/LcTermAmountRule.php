<?php

namespace App\Rules;

use App\Models\AccountType;
use App\Models\LetterOfCreditIssuance;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Facades\DB;

class LcTermAmountRule implements ImplicitRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
	protected $category_name, $lc_cash_cover_amount , $lc_commission_amount ,$issuance_date, $min_lc_commission_fees,$issuance_fees ,$account_type_id,$account_id,$company_id,$financial_institution_id;
    public function __construct($categoryName ,$accountTypeId,$accountId ,$issuanceDate,$lcCashCoverAmount,$lcCommissionAmount,$minLcCommissionFees,$issuanceFees,$companyId,$financialInstitutionId)
    {
		$this->category_name = $categoryName;
		$this->account_type_id = $accountTypeId;
		$this->lc_cash_cover_amount = number_unformat($lcCashCoverAmount);
		$this->lc_commission_amount = number_unformat($lcCommissionAmount);
		$this->min_lc_commission_fees = number_unformat($minLcCommissionFees);
		$this->issuance_fees = number_unformat($issuanceFees);
		$this->account_id = $accountId;
		$this->company_id = $companyId;
		$this->financial_institution_id = $financialInstitutionId;
		$this->issuance_date = $issuanceDate;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
		$accountTypeId = $this->account_type_id ;
		if(is_null($accountTypeId)){
			return true ;
		}
		$accountType = AccountType::find($accountTypeId);
		if(!$accountType->isCurrentAccount()){
			return true ; 
		}
		if($this->category_name == LetterOfCreditIssuance::OPENING_BALANCE){
			return true ;
		}
		$accountId = $this->account_id ;
		$statementDate = $this->issuance_date;
		$accountNumberModel =  ('\App\Models\\'.$accountType->getModelName())::find($accountId);
		$statementTableName = (get_class($accountNumberModel)::getStatementTableName()) ;
		$foreignKeyName = get_class($accountNumberModel)::getForeignKeyInStatementTable();
		

		$balanceRow = DB::table($statementTableName)->where($foreignKeyName,$accountNumberModel->id)->whereDate('date','<=' , $statementDate)->orderByRaw('date desc,id desc')->first();
		$currentAccountBalanceAtIssuanceDate = $balanceRow ? $balanceRow->end_balance : 0 ;
        $maxBetweenCommissionAmountAndFees = max($this->lc_commission_amount , $this->min_lc_commission_fees);
		return $this->lc_cash_cover_amount + $maxBetweenCommissionAmountAndFees  + $this->issuance_fees <= $currentAccountBalanceAtIssuanceDate;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('There Is No Enough Balance In Current Account To Apply LC Cash Cover And Commission');
    }
}
