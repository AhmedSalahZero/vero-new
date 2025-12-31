<?php
namespace App\Traits\Models;

use App\Models\AccountType;
use App\Models\CleanOverdraft;
use App\Models\FinancialInstitutionAccount;
use App\Models\FullySecuredOverdraft;
use App\Models\LetterOfCreditIssuance;
use App\Models\OverdraftAgainstAssignmentOfContract;
use App\Models\OverdraftAgainstCommercialPaper;

trait HasCreditStatements
{
		/**
	 * * هنا لو اليوزر ضاف فلوس في الحساب
	 * * بنحطها في الاستيت منت
	 * * سواء كانت كاش استيتمنت او بانك استيتمنت علي حسب نوع الحساب او الحركة يعني
	 */
	public function handleCreditStatement(int $companyId , $bankId = null ,?AccountType $accountType = null , ?string $accountNumber = null,?string $moneyType = null,?string $statementDate = null,?float $paidAmount = null,$deliveryBranchId=null,?string $currencyName = null , ?string $commentEn = null , ?string $commentAr = null , ?string $type = null)
	{
		if($accountType && $accountType->getSlug() == AccountType::CLEAN_OVERDRAFT){
			$cleanOverdraft  = CleanOverdraft::findByAccountNumber($accountNumber,$companyId,$bankId);
			$this->storeCleanOverdraftCreditBankStatement($moneyType,$cleanOverdraft,$statementDate,$paidAmount,$commentEn,$commentAr);
		}
		elseif($accountType && $accountType->getSlug() == AccountType::FULLY_SECURED_OVERDRAFT){
			$fullySecuredOverdraft  = FullySecuredOverdraft::findByAccountNumber($accountNumber,$companyId,$bankId);
			$this->storeFullySecuredOverdraftCreditBankStatement($moneyType,$fullySecuredOverdraft,$statementDate,$paidAmount,$commentEn,$commentAr);
		}
		elseif($accountType && $accountType->getSlug() == AccountType::OVERDRAFT_AGAINST_COMMERCIAL_PAPER){
			$overdraftAgainstCommercialPaper  = OverdraftAgainstCommercialPaper::findByAccountNumber($accountNumber,$companyId,$bankId);
			$this->storeOverdraftAgainstCommercialPaperCreditBankStatement($moneyType,$overdraftAgainstCommercialPaper,$statementDate,$paidAmount,$commentEn,$commentAr);
		}
		elseif($accountType && $accountType->getSlug() == AccountType::OVERDRAFT_AGAINST_ASSIGNMENT_OF_CONTRACTS){
			$odAgainstAssignmentOfContract  = OverdraftAgainstAssignmentOfContract::findByAccountNumber($accountNumber,$companyId,$bankId);
			$this->storeOverdraftAgainstAssignmentOfContractCreditBankStatement($moneyType,$odAgainstAssignmentOfContract,$statementDate,$paidAmount,$commentEn,$commentAr);
		}
		elseif($accountType && $accountType->getSlug() == AccountType::CURRENT_ACCOUNT){
			$financialInstitutionAccount = FinancialInstitutionAccount::findByAccountNumber($accountNumber,$companyId,$bankId);
			$this->storeCurrentAccountCreditBankStatement($statementDate,$paidAmount,$financialInstitutionAccount->id,$commentEn,$commentAr,$type);
		}
		elseif($this->isCashPayment()){
			$this->storeCashInSafeCreditStatement($statementDate,$paidAmount,$currencyName,$deliveryBranchId,$commentEn,$commentAr);
		}
	}

	public function storeCashInSafeCreditStatement(string $date , $paidAmount , string $currencyName,int $branchId,?string $commentEn , ?string $commentAr)
	{
		/**
		 * @var MoneyPayment $this
		 */
		return $this->cashInSafeCreditStatement()->create([
			'branch_id'=>$branchId,
			'currency'=>$currencyName ,
			'company_id'=>$this->company_id ,
			'credit'=>$paidAmount,
			'date'=>$date,
			'comment_en'=>$commentEn , 
			'comment_ar'=>$commentAr
		]);
	}

	public function storeCurrentAccountCreditBankStatement(string $date , $paidAmount , int $financialInstitutionAccountId,?string $commentEn = null , ?string $commentAr = null , ?string $type = null )
	{
		return $this->currentAccountCreditBankStatement()->create([
			'financial_institution_account_id'=>$financialInstitutionAccountId ,
			'company_id'=>$this->company_id?:getCurrentCompanyId() ,
			'credit'=>$paidAmount,
			'date'=>$date,
			'comment_en'=>$commentEn,
			'comment_ar'=>$commentAr,
			'type'=>$type
		]);
	}

	public function storeCleanOverdraftCreditBankStatement(string $moneyType , CleanOverdraft $cleanOverdraft , string $date , $paidAmount,?string $commentEn = null , ?string $commentAr )
	{
		return  $this->cleanOverdraftCreditBankStatement()->create([
			'type'=>$moneyType ,
			'clean_overdraft_id'=>$cleanOverdraft->id ,
			'company_id'=>$this->company_id ,
			'date'=>$date,
			'limit'=>$cleanOverdraft->getLimit(),
			'beginning_balance'=>0 ,
			'debit'=>0,
			'credit'=>$paidAmount,
			'comment_en'=>$commentEn,
			'comment_ar'=>$commentAr
		]) ;

	}
	public function storeFullySecuredOverdraftCreditBankStatement(string $moneyType , FullySecuredOverdraft $fullySecuredOverdraft , string $date , $paidAmount,?string $commentEn = null , ?string $commentAr )
	{
		return  $this->fullySecuredOverdraftCreditBankStatement()->create([
			'type'=>$moneyType ,
			'fully_secured_overdraft_id'=>$fullySecuredOverdraft->id ,
			'company_id'=>$this->company_id ,
			'date'=>$date,
			'limit'=>$fullySecuredOverdraft->getLimit(),
			'beginning_balance'=>0 ,
			'debit'=>0,
			'credit'=>$paidAmount,
			'comment_en'=>$commentEn,
			'comment_ar'=>$commentAr
		]) ;

	}
	public function storeOverdraftAgainstCommercialPaperCreditBankStatement(string $moneyType , OverdraftAgainstCommercialPaper $overdraftAgainstCommercialPaper , string $date , $paidAmount , ?string $commentEn = null , ?string $commentAr )
	{
		return  $this->overdraftAgainstCommercialPaperCreditBankStatement()->create([
			'type'=>$moneyType ,
			'overdraft_against_commercial_paper_id'=>$overdraftAgainstCommercialPaper->id ,
			'company_id'=>$this->company_id ,
			'date'=>$date,
			'limit'=>$overdraftAgainstCommercialPaper->getLimit(),
			'beginning_balance'=>0 ,
			'debit'=>0,
			'credit'=>$paidAmount,
			'comment_en'=>$commentEn,
			'comment_ar'=>$commentAr
		]) ;

	}
	
	public function storeOverdraftAgainstAssignmentOfContractCreditBankStatement(string $moneyType , OverdraftAgainstAssignmentOfContract $odAgainstAssignmentOfContract , string $date , $paidAmount , ?string $commentEn = null , ?string $commentAr )
	{
		return  $this->overdraftAgainstAssignmentOfContractCreditBankStatement()->create([
			'type'=>$moneyType ,
			'overdraft_against_assignment_of_contract_id'=>$odAgainstAssignmentOfContract->id ,
			'company_id'=>$this->company_id ,
			'date'=>$date,
			'limit'=>$odAgainstAssignmentOfContract->getLimit(),
			'beginning_balance'=>0 ,
			'debit'=>0,
			'credit'=>$paidAmount,
			'comment_en'=>$commentEn,
			'comment_ar'=>$commentAr
		]) ;

	}
	
	
}
