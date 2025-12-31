<?php
namespace App\Traits\Models;



trait HasLetterOfCreditCashCoverStatements
{
    public function generateLetterOfCreditCashCoverData( int $financialInstitutionId , string $source  , int $lcFacilityId,string $lcType,$companyId,string $date,$beginningBalance,$debit , $credit,string $currencyName , int $lcAdvancedPaymentHistoryId =0 , $type =null):array
    {
        return [
			'type'=>$type , // beginning-balance for example
			'lc_facility_id'=>$lcFacilityId ,
			'source'=>$source,
			'financial_institution_id'=>$financialInstitutionId,
			'lc_type'=>$lcType ,
			'lc_advanced_payment_history_id'=>$lcAdvancedPaymentHistoryId,
			'currency'=>$currencyName ,
			'company_id'=>$companyId ,
			'beginning_balance'=>$beginningBalance,
			'debit'=>$debit,
			'credit'=>$credit ,
			'date'=>$date,
		];
    }
		/**
	 * * هنا لو اليوزر ضاف فلوس في الحساب
	 * * بنحطها في الاستيت منت
	 * * سواء كانت كاش استيتمنت او بانك استيتمنت علي حسب نوع الحساب او الحركة يعني
	 */

	public function handleLetterOfCreditCashCoverStatement(int $financialInstitutionId , string $source  , int $lcFacilityId,string $lcType,$companyId,string $date,$beginningBalance,$debit , $credit,string $currencyName ,int $lcAdvancedPaymentHistoryId = 0, $type =null)
	{
		$data = $this->generateLetterOfCreditCashCoverData($financialInstitutionId , $source  , $lcFacilityId, $lcType,$companyId,$date,$beginningBalance,$debit , $credit,$currencyName ,$lcAdvancedPaymentHistoryId, $type) ;
		$this->letterOfCreditCashCoverStatements()->create($data);

	}

	
	public function storeCurrentAccountDebitBankStatement(string $date , $debit , int $financialInstitutionAccountId , int $lcAdvancedPaymentHistoryId = 0 , int $letterOfCreditIssuanceId = 0)
	{
		return $this->currentAccountDebitBankStatement()->create([
			'financial_institution_account_id'=>$financialInstitutionAccountId,
			'company_id'=>$this->company_id ,
			'credit'=>0,
			'debit'=>$debit,
			'lc_advanced_payment_history_id'=>$lcAdvancedPaymentHistoryId,
			'letter_of_guarantee_issuance_id'=>$letterOfCreditIssuanceId,
			'date'=>$date,
		]);
	}
	public function storeCurrentAccountCreditBankStatement(string $date , $credit , int $financialInstitutionAccountId , int $lcAdvancedPaymentHistoryId = 0 ,  $isActive = 1 , ?string $commentEn = null, ?string $commentAr = null , bool $isRenewalFees = false, bool $isCommissionFees = false , int $lcRenewalDateHistoryId = null,int $isIssuanceFees = 0)
	{
		return $this->currentAccountCreditBankStatement()->create([
			'financial_institution_account_id'=>$financialInstitutionAccountId,
			'company_id'=>$this->company_id ,
			'lc_advanced_payment_history_id'=>$lcAdvancedPaymentHistoryId,
			'is_active'=>$isActive , // is active خاصة بجزئيه ال commission فقط
			'credit'=>$credit,
			'debit'=>0,
			'date'=>$date,
			'comment_en'=>$commentEn,
			'comment_ar'=>$commentAr,
			'is_commission_fees'=>$isCommissionFees,
			'is_issuance_fees'=>$isIssuanceFees
		]);
	}
	
}
