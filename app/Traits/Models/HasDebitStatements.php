<?php 
namespace App\Traits\Models;

use App\Models\AccountType;
use App\Models\CleanOverdraft;
use App\Models\FinancialInstitutionAccount;
use App\Models\FullySecuredOverdraft;
use App\Models\OverdraftAgainstAssignmentOfContract;
use App\Models\OverdraftAgainstCommercialPaper;

trait HasDebitStatements 
{
	/**
	 * * هنا لو اليوزر ضاف فلوس في الحساب
	 * * بنحطها في الاستيت منت
	 * * سواء كانت كاش استيتمنت او بانك استيتمنت علي حسب نوع الحساب او الحركة يعني
	 */
	public function handleDebitStatement(?int $financialInstitutionId = 0 ,?AccountType $accountType = null , ?string $accountNumber = null,?string $moneyType = null,?string $date = null,?float $debit = 0,?string $currencyName = null,?int $receivingBranchId = null,$exchangeRate=1 , $commentEn = null , $commentAr = null,$isPeriodInterest=false , $isBreakInterest = false )
	{
		if($accountType && $accountType->getSlug() == AccountType::CLEAN_OVERDRAFT){
			$cleanOverdraft  = CleanOverdraft::findByAccountNumber($accountNumber,getCurrentCompanyId(),$financialInstitutionId);
			return $this->storeCleanOverdraftDebitBankStatement($moneyType,$cleanOverdraft,$date,$debit);
		}
		if($accountType && $accountType->getSlug() == AccountType::FULLY_SECURED_OVERDRAFT){
			$fullySecuredOverdraft  = FullySecuredOverdraft::findByAccountNumber($accountNumber,getCurrentCompanyId(),$financialInstitutionId);
			return $this->storeFullySecuredOverdraftDebitBankStatement($moneyType,$fullySecuredOverdraft,$date,$debit);
		}
		if($accountType && $accountType->getSlug() == AccountType::OVERDRAFT_AGAINST_COMMERCIAL_PAPER){
			$overdraftAgainstCommercialPaper  = OverdraftAgainstCommercialPaper::findByAccountNumber($accountNumber,getCurrentCompanyId(),$financialInstitutionId);
			return $this->storeOverdraftAgainstCommercialPaperDebitBankStatement($moneyType,$overdraftAgainstCommercialPaper,$date,$debit);
		}
		if($accountType && $accountType->getSlug() == AccountType::OVERDRAFT_AGAINST_ASSIGNMENT_OF_CONTRACTS){
			$odAgainstAssignmentOfContract  = OverdraftAgainstAssignmentOfContract::findByAccountNumber($accountNumber,getCurrentCompanyId(),$financialInstitutionId);
			return $this->storeOverdraftAgainstAssignmentOfContractDebitBankStatement($moneyType,$odAgainstAssignmentOfContract,$date,$debit);
		}
		elseif($accountType && $accountType->getSlug() == AccountType::CURRENT_ACCOUNT){
			$financialInstitutionAccount = FinancialInstitutionAccount::findByAccountNumber($accountNumber,getCurrentCompanyId(),$financialInstitutionId);
			return $this->storeCurrentAccountDebitBankStatement($date,$debit,$financialInstitutionAccount->id,false,$commentEn,$commentAr,$isPeriodInterest,$isBreakInterest);
		}
		elseif($this->isCashInSafe()){
			return $this->storeCashInSafeDebitStatement($date,$debit,$currencyName,$receivingBranchId,$exchangeRate);
		}
	}
	
	public function storeCleanOverdraftDebitBankStatement(string $moneyType , CleanOverdraft $cleanOverdraft , string $date , $debit )
	{
		return $this->cleanOverdraftDebitBankStatement()->create([
			'type'=>$moneyType ,
			'clean_overdraft_id'=>$cleanOverdraft->id ,
			'company_id'=>$this->company_id ,
			'date'=>$date,
			'limit'=>$cleanOverdraft->getLimit(),
			'beginning_balance'=>0 ,
			'debit'=>$debit,
			'credit'=>0
		]) ;
	}
	public function storeFullySecuredOverdraftDebitBankStatement(string $moneyType , FullySecuredOverdraft $fullySecuredOverdraft , string $date , $debit )
	{
		return $this->fullySecuredOverdraftDebitBankStatement()->create([
			'type'=>$moneyType ,
			'fully_secured_overdraft_id'=>$fullySecuredOverdraft->id ,
			'company_id'=>$this->company_id ,
			'date'=>$date,
			'limit'=>$fullySecuredOverdraft->getLimit(),
			'beginning_balance'=>0 ,
			'debit'=>$debit,
			'credit'=>0
		]) ;
	}
	public function storeOverdraftAgainstCommercialPaperDebitBankStatement(string $moneyType , OverdraftAgainstCommercialPaper $overdraftAgainstCommercialPaper , string $date , $debit )
	{

		return $this->overdraftAgainstCommercialPaperDebitBankStatement()->create([
			'type'=>$moneyType ,
			'overdraft_against_commercial_paper_id'=>$overdraftAgainstCommercialPaper->id ,
			'company_id'=>$this->company_id ,
			'date'=>$date,
			'limit'=>$overdraftAgainstCommercialPaper->getLimit(),
			'beginning_balance'=>0 ,
			'debit'=>$debit,
			'credit'=>0
		]) ;
	}
	
	public function storeOverdraftAgainstAssignmentOfContractDebitBankStatement(string $moneyType , OverdraftAgainstAssignmentOfContract $odAgainstAssignmentOfContract , string $date , $debit )
	{

		return $this->overdraftAgainstAssignmentOfContractDebitBankStatement()->create([
			'type'=>$moneyType ,
			'overdraft_against_assignment_of_contract_id'=>$odAgainstAssignmentOfContract->id ,
			'company_id'=>$this->company_id ,
			'date'=>$date,
			'limit'=>$odAgainstAssignmentOfContract->getLimit(),
			'beginning_balance'=>0 ,
			'debit'=>$debit,
			'credit'=>0
		]) ;
	}
	
	public function storeCashInSafeDebitStatement(string $date , $debit , string $currencyName,int $branchId,$exchangeRate)
	{
		return $this->cashInSafeDebitStatement()->create([
			'branch_id'=>$branchId,
			'currency'=>$currencyName ,
			'exchange_rate'=>$exchangeRate,
			'company_id'=>$this->company_id ,
			'debit'=>$debit,
			'credit'=>0,
			'date'=>$date,
		]);
	}	
	public function storeCurrentAccountDebitBankStatement(string $date , $debit , int $financialInstitutionAccountId , bool $isTdRenewal = false  , string $commentEn = null , string $commentAr = null,$isPeriodCdOrTdInterest = false  , $isBreakInterest =false )
	{
		return $this->currentAccountDebitBankStatement()->create([
			'financial_institution_account_id'=>$financialInstitutionAccountId,
			'company_id'=>$this->company_id ,
			'credit'=>0,
			'debit'=>$debit,
			'date'=>$date,
			'is_td_renewal'=>$isTdRenewal,
			'comment_en'=>$commentEn,
			'comment_ar'=>$commentAr,
			'is_period_cd_or_td_interest'=>$isPeriodCdOrTdInterest,
			'is_break_interest'=>$isBreakInterest
		]);
	}	
	
}
