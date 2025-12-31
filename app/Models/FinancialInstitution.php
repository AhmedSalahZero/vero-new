<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\CertificatesOfDeposit;
use App\Models\CleanOverdraft;
use App\Models\FinancialInstitutionAccount;
use App\Models\OverdraftAgainstCommercialPaper;
use App\Services\Api\OdooService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialInstitution extends Model
{
    protected $guarded = ['id'];

	const BANK = 'bank';
	protected $with = [
		'bank'
	];

	public function scopeOnlyForCompany(Builder $builder , int $companyId){
		return $builder->where('company_id',$companyId);
	}

	public function scopeOnlyForSource(Builder $builder , string $source)
	{
		if($source === LetterOfGuaranteeIssuance::LG_FACILITY){
			return $builder->has('LetterOfGuaranteeFacilities');
		}
		if($source === LetterOfGuaranteeIssuance::AGAINST_CD){
			return $builder->has('certificatesOfDeposits');
		}
		if($source === LetterOfGuaranteeIssuance::AGAINST_TD){
			return $builder->has('timeOfDeposits');
		}
		if($source === LetterOfGuaranteeIssuance::HUNDRED_PERCENTAGE_CASH_COVER ){
			return $builder;
		}
		
		if($source === LetterOfCreditIssuance::LC_FACILITY){
			return $builder->has('LetterOfCreditFacilities');
		}
		
		throw new \Exception('custom exception .. invalid source for financial institution');
	}

	public function scopeOnlyBanks(Builder $builder)
	{
		$builder->where('type',self::BANK);
	}
	public function scopeOnlyHasCleanOverdrafts(Builder $builder){
		$builder
		->has('cleanOverdrafts');
	}
	public function scopeOnlyHasOverdraftAgainstCommercialPapers(Builder $builder){
		$builder
		->has('overdraftAgainstCommercialPapers');
	}
	public function scopeOnlyHasOverdraftAgainstAssignmentOfContracts(Builder $builder){
		$builder
		->has('overdraftAgainstAssignmentOfContracts');
	}
	public function scopeOnlyHasFullySecuredOverdrafts(Builder $builder){
		$builder
		->has('fullySecuredOverdrafts');
	}
	public function scopeOnlyHasOverdrafts(Builder $builder){
		$builder
		->has('cleanOverdrafts')
		->orHas('fullySecuredOverdrafts')
		->orHas('overdraftAgainstCommercialPapers');
	}
	public function scopeOnlyCompany(Builder $query,$companyId){
		return $query->where('company_id',$companyId);
	}
	public function scopeOnlyHasMediumTermLoans(Builder $builder,string $currency){
	
		$builder
		->whereHas('loans',function($builder) use ($currency){
			$builder->where('currency',$currency);
		});
	}
	
	/**
	 * * نوع المؤسسة المالية وليكن مثلا بنك
	 */
	public function getType():string
	{
		return $this->type ;
	}
    public function isBank():bool
    {
        return $this->getType() == self::BANK;
    }
    public function isLeasingCompanies():bool
    {
        return $this->getType() =='leasing_companies';
    }
    public function isFactoringCompanies():bool
    {
        return $this->getType() =='factoring_companies';
    }
	public function isMortgageCompanies():bool
    {
        return $this->getType() =='mortgage_companies';
    }
	public function getName()
	{
		
		return $this->isBank() ? $this->getBankName() : $this->name ;
	}
	public function getBranchName()
	{
		return $this->branch_name ;
	}
	/**
	 * * هو رقم مميز للحساب الرئيسي زي ال الاي دي وبالتالي هو يختلف عن رقم الحساب نفسه
	 */
	public function getCompanyAccountNumber()
	{
		return $this->company_account_number ;
	}
	/**
	 * * تاريخ المبالغ الماليه اللي معايا في حساباتي في المؤسسة المالية دي
	 */
	// public function getBalanceDate()
	// {
	// 	return $this->balance_date ;
	// }
	// public function getBalanceDateFormatted()
	// {
	// 	$balanceDate = $this->getBalanceDate();
	// 	return $balanceDate ? Carbon::make($balanceDate)->format('d-m-Y') : null;
	// }
	public function getBankId()
    {
        return $this->bank_id ;
    }
	public function bank()
	{
		return $this->belongsTo(Bank::class ,'bank_id','id');
	}
	public function getBankName()
	{
		 return $this->bank ? $this->bank->getViewName() : __('N/A');
	}
	public function getBankNameIn(string $lang)
	{
		 return $this->bank ? $this->bank['name_'.$lang] : __('N/A');
	}
	public function accounts():HasMany
	{
		return $this->hasMany(FinancialInstitutionAccount::class,'financial_institution_id','id');
	}

	public function certificatesOfDeposits()
	{
		return $this->hasMany(CertificatesOfDeposit::class , 'financial_institution_id','id');
	}
	public function timeOfDeposits()
	{
		return $this->hasMany(TimeOfDeposit::class , 'financial_institution_id','id');
	}
	public function cleanOverdrafts()
	{
		return $this->hasMany(CleanOverdraft::class , 'financial_institution_id','id');
	}
	public function fullySecuredOverdrafts()
	{
		return $this->hasMany(FullySecuredOverdraft::class , 'financial_institution_id','id');
	}
	public function overdraftAgainstCommercialPapers()
	{
		return $this->hasMany(OverdraftAgainstCommercialPaper::class , 'financial_institution_id','id');
	}
	public function overdraftAgainstAssignmentOfContracts()
	{
		return $this->hasMany(OverdraftAgainstAssignmentOfContract::class , 'financial_institution_id','id');
	}
	/**
	 * * use getCurrentAvailableLetterOfGuaranteeFacility instead
	 */
	public function LetterOfGuaranteeFacilities()
	{
		return $this->hasMany(LetterOfGuaranteeFacility::class , 'financial_institution_id','id');
	}
	public function scopeOnlyHasLgFacility($builder)
	{
		return $builder->whereHas('LetterOfGuaranteeFacilities',function($builder){
			$builder->where('contract_end_date','>=',now());
		});
	}

	public function LetterOfCreditFacilities()
	{
		return $this->hasMany(LetterOfCreditFacility::class , 'financial_institution_id','id');
	}
	
	public function storeNewAccounts(array $accounts,Company $company)
	{
		
		foreach($accounts as $index=>$accountArr){
			$balanceAmount = $accountArr['balance_amount'] ?? 0 ;
			$balanceDate = $accountArr['balance_date'];
			$currentBalanceDate = $balanceDate ? Carbon::make($balanceDate)->format('Y-m-d'):null;

			if($currentBalanceDate){
				/**
				 * @var FinancialInstitutionAccount $account
				 */
				$account = $this->accounts()->create([
					'account_number'=>$accountArr['account_number'],
					'odoo_code'=>$odooCode = $accountArr['odoo_code']??null,
					'balance_amount'=>$balanceAmount ,
					'exchange_rate'=>$accountArr['exchange_rate'],
					'currency'=> $accountArr['currency'],
					'iban'=>$accountArr['iban'],
					'balance_date'=>$currentBalanceDate,
					'company_id'=>getCurrentCompanyId(),
				]);
			//	$endDate = Carbon::make($balanceDate)->addYear(FinancialInstitutionAccount::NUMBER_OF_YEARS_FOR_INTEREST_IN_CURRENT_STATEMENT)->format('Y-m-d');
			//	$account->handleEndOfMonthInterest($balanceDate,$endDate,$company->id);
			}
				
			/**
			 * * لو ال
			 * * balance amount > 0
			 * * هنضفله قيمة في ال
			 * * current account bank Statement
			 */
			// $startDate = isset($accountArr['start_date']) && $accountArr['start_date'] ? Carbon::make($accountArr['start_date'])->format('Y-m-d') : $startDate;
			if($currentBalanceDate){
				$account->currentAccountBankStatements()->create([
					'company_id'=>getCurrentCompanyId() ,
					'beginning_balance'=>0,
					'is_beginning_balance'=>1 ,
					'debit'=>$balanceAmount,
					'is_debit'=>$isDebit =$balanceAmount >= 0 ,
					'is_credit' => !$isDebit,
					'date'=>$currentBalanceDate ,
					'comment_en'=>__('Beginning Balance',[],'en'),
					'comment_ar'=>__('Beginning Balance',[],'ar'),
				]);
			}
			$account->accountInterests()->create([
				'interest_rate'=>$accountArr['interest_rate'],
				'min_balance'=>$accountArr['min_balance'],
				'start_date'=>$currentBalanceDate
			]);
			
			$account->updateBankStatementsFromDate($currentBalanceDate);
			if($company->hasOdooIntegrationCredentials()){
				$odoo = new OdooService($company);
				$odoo->syncFinancialInstitutions($account);
			}
		}
		
			
		
	}
	
	public function runningCertificatesOfDeposits()
	{
		return $this->hasMany(CertificatesOfDeposit::class , 'financial_institution_id','id')
		->where('status',CertificatesOfDeposit::RUNNING);
	}
	public function maturedCertificatesOfDeposits()
	{
		return $this->hasMany(CertificatesOfDeposit::class , 'financial_institution_id','id')
		->where('status',CertificatesOfDeposit::MATURED);
	}
	public function brokenCertificatesOfDeposits()
	{
		return $this->hasMany(CertificatesOfDeposit::class , 'financial_institution_id','id')
		->where('status',CertificatesOfDeposit::BROKEN);
	}





	public function runningTimeOfDeposits()
	{
		return $this->hasMany(TimeOfDeposit::class , 'financial_institution_id','id')
		->where('status',TimeOfDeposit::RUNNING);
	}
	public function maturedTimeOfDeposits()
	{
		return $this->hasMany(TimeOfDeposit::class , 'financial_institution_id','id')
		->where('status',TimeOfDeposit::MATURED);
	}
	public function brokenTimeOfDeposits()
	{
		return $this->hasMany(TimeOfDeposit::class , 'financial_institution_id','id')
		->where('status',TimeOfDeposit::BROKEN);
	}
	public function letterOfCreditIssuances():HasMany
	{
		return $this->hasMany(LetterOfCreditIssuance::class ,'financial_institution_id','id');
	}	
	public function getAllAccountNumbers():array 
	{
		$currentAccountNumber = $this->accounts->pluck('account_number')->toArray();
		$cleanOverdraftAccount = $this->cleanOverdrafts->pluck('account_number')->toArray();
		$fullySecuredOverdraftAccount = $this->fullySecuredOverdrafts->pluck('account_number')->toArray();
		$overdraftAgainstCommercialPaperAccount = $this->overdraftAgainstCommercialPapers->pluck('account_number')->toArray();
		$overdraftAgainstAssignmentOfContractsAccount = $this->overdraftAgainstAssignmentOfContracts->pluck('account_number')->toArray();
		$certificatesOfDepositsAccount = $this->certificatesOfDeposits->pluck('account_number')->toArray();
		$timeOfDepositsAccount = $this->timeOfDeposits->pluck('account_number')->toArray();
		return array_merge(
			$currentAccountNumber ,
			$cleanOverdraftAccount ,
			$fullySecuredOverdraftAccount,
			$overdraftAgainstCommercialPaperAccount,
			$overdraftAgainstAssignmentOfContractsAccount,
			$certificatesOfDepositsAccount,
			$timeOfDepositsAccount
		) ;
	}
	public function loans()
	{
		return $this->hasMany(MediumTermLoan::class,'financial_institution_id','id');
	}
	public  function getOpeningBalanceForAccount( int $accountTypeId , string $accountNumber
	// ,string $currencyName
	 ){
		$accountModel = $this->getAccountFromTypeAndNumber($accountTypeId,$accountNumber);
		return $accountModel instanceof FinancialInstitutionAccount ? $accountModel->getOpeningBalanceDate() : $accountModel->getContractStartDate();
	}
	public function getAccountFromTypeAndNumber($accountTypeId,$accountNumber)
	{
		/**
		 * @var AccountType $accountType 
		 */
		$accountType = AccountType::find($accountTypeId);
		$accountTypeModelName = $accountType->getModelName();
		/**
		 * @var CleanOverdraft|FinancialInstitutionAccount $accountModel 
		 */
		$fullModelName = 'App\Models\\'.$accountTypeModelName ;
	
		return  $fullModelName::where([
			['financial_institution_id','=',$this->id],
			['account_number','=',$accountNumber],
			['company_id','=',$this->company_id]
		])->first();
	}
	public  function getOdooPaymentIds( int $accountTypeId , string $accountNumber):array{
		$accountModel = $this->getAccountFromTypeAndNumber($accountTypeId,$accountNumber);
		return [
			'odoo_inbound_transfer_payment_method_id'=>$accountModel->getOdooInboundTransferPaymentMethodId(), // add HasOdooPaymentMethod Trait to CleanOverdraft and so on with the columns migration
			'odoo_outbound_transfer_payment_method_id'=>$accountModel->getOdooOutboundTransferPaymentMethodId(),
			'odoo_inbound_cheque_payment_method_id'=>$accountModel->getOdooInboundChequePaymentMethodId(),
			'odoo_outbound_cheque_payment_method_id'=>$accountModel->getOdooOutboundChequePaymentMethodId()
		];
		// return $accountModel instanceof FinancialInstitutionAccount ? $accountModel->getOpeningBalanceDate() : $accountModel->getContractStartDate();
	}
	public  function getOdooIdForAccount( int $accountTypeId , string $accountNumber){
		$accountModel = $this->getAccountFromTypeAndNumber($accountTypeId,$accountNumber);
		
		return $accountModel->getOdooId();
	}
	
	
	public  function getJournalIdForAccount( int $accountTypeId , string $accountNumber){
		$accountModel = $this->getAccountFromTypeAndNumber($accountTypeId,$accountNumber);
		return  $accountModel->getJournalId();
	}
}
