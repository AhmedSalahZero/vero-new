<?php

namespace App;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class OdooSetting extends Model
{
    protected $guarded = ['id'];
	
	public function getId()
	{
		return $this->id;
	}
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}
	public  function getLiquidityAccountOdooId():int
	{
		return $this->liquidity_transfer_account_id; 
	}
	public function getChequesReceivableCode()
	{
		return $this->cheques_receivable_code;
	}
	public function getChequesReceivableId()
	{
		return $this->cheques_receivable_id;
	}
		public function getChequesPayableCode()
	{
		return $this->cheques_payable_code;
	}
	public function getChequesPayableId()
	{
		return $this->cheques_payable_id;
	}
	public function getLgCashCoverCode()
	{
		return $this->lg_cash_cover_code;
	}
	public function getLgCashCoverId()
	{
		return $this->lg_cash_cover_id;
	}
	public function getLcCashCoverCode()
	{
		return $this->lc_cash_cover_code;
	}
	public function getLcCashCoverId()
	{
		return $this->lc_cash_cover_id;
	}
	public static function getSuspenseAccountId():int
	{
		return OdooSetting::where('company_id',getCurrentCompanyId())->first()->suspense_account_id ; 
	}
	public function getInterestRevenueOdooId()
	{
		return $this->interest_revenue_id;
	}
	public function getCustodyAccountId():int 
	{
		if(!$this->custody_account_id){
			throw new \Exception('Custody Account Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->custody_account_id;
	}
	public function getEmployeeLoanAccountId():int 
	{
		if(!$this->employee_loans_account_id){
			throw new \Exception('Employee Loan Account Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->employee_loans_account_id;
	}
	public function getShareholderAccount():int 
	{
		if(!$this->shareholder_account_id){
			throw new \Exception('Shareholder Account Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->shareholder_account_id;
	}
	// public function getSisterCompanyAccount():int 
	// {
	// 	if(is_null($this->sister_company_account_id)){
	// 		throw new \Exception('Sister Company Account Not Found .. Please Add It From Other Odoo Setting Form');
	// 	}
	// 	return  $this->sister_company_account_id;
	// }
	public function getDividendPaymentAccount():int 
	{
		if(is_null($this->dividend_payable_account_id)){
			throw new \Exception('Dividend Account Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->dividend_payable_account_id;
	}
	public function getInsuranceFromAccount():int 
	{
		if(is_null($this->insurance_from_account_id)){
			throw new \Exception('Insurance From Account Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->insurance_from_account_id;
	}
	public function getInsuranceToAccount():int 
	{
		if(!$this->insurance_to_account_id){
			throw new \Exception('Insurance To Account Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->insurance_to_account_id;
	}
	public function getLetterOfGuaranteeIssuanceFeesId():int 
	{
		if(!$this->letter_of_guarantee_issuance_fees_id){
			throw new \Exception('Letter Of Guarantee Issuance Fees Id Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->letter_of_guarantee_issuance_fees_id;
	}
	public function getLetterOfGuaranteeCommissionFeesId():int 
	{
		if(!$this->letter_of_guarantee_commission_fees_id){
			throw new \Exception('Letter Of Guarantee Issuance Fees Id Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->letter_of_guarantee_commission_fees_id;
	}

	
}
