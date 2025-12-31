<?php

namespace App\Models;

use App\Models\FinancialInstitutionAccount;
use App\Traits\HasCompany;
use App\Traits\HasDepositAccount;
use App\Traits\HasLastStatementAmount;
use App\Traits\HasPeriodicInterest;
use App\Traits\Models\HasBlockedAgainst;
use App\Traits\Models\HasCreditStatements;
use App\Traits\Models\HasDebitStatements;
use App\Traits\Models\HasOdooMoneyTransfer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
	 * * توفر شهادات الإيداع ) (CDsللمدخر ين طر يقة لكسب معدل فائدة أعلى على مدخراتك مقابل الموافقة على حجز
	**    أموالك لفترة زمنية محددة - مع الحفاظ على أموالك آمنة بفضل حمايتها من البنك المركزي
	 */
class CertificatesOfDeposit extends Model
{
	use HasDebitStatements,HasCreditStatements,HasBlockedAgainst,HasLastStatementAmount,HasDepositAccount,HasOdooMoneyTransfer,HasCompany,HasPeriodicInterest ;
    protected $guarded = ['id'];
	const RUNNING = 'running';
	const MATURED = 'matured';
	const BROKEN = 'broken';
	public static function getAllTypes()
	{
		return [
			self::RUNNING,
			self::MATURED,
			self::BROKEN
		];
	}

	public function getStatus()
	{
		return $this->status ;
	}
	public function isRunning()
	{
		return $this->getStatus() === self::RUNNING;
	}
	/**
	 * * معناه انها خلص استوفيت وهتاخد قيمة الفايدة وقيمة الشهادة
	 */
	public function isMatured()
	{
		return $this->getStatus() === self::MATURED;
	}
	/**
	 * * معناه انك قررت تكسرها قبل فتره انتهائها وبالتالي هتاخد قيمتها بس هتدفع فايدة ورسوم الخ
	 */
	public function isBroken()
	{
		return $this->getStatus() === self::BROKEN;
	}

	public function getStartDate()
	{
		return $this->start_date;
	}
	public function getStartDateFormatted()
	{
		$startDate = $this->start_date ;
		return $startDate ? Carbon::make($startDate)->format('d-m-Y'):null ;
	}
	public function getDepositDate()
	{
		return $this->deposit_date;
	}
	public function getDepositDateFormatted()
	{
		$depositDate = $this->deposit_date ;
		return $depositDate ? Carbon::make($depositDate)->format('d-m-Y'):null ;
	}
	/**
	 * * تاريخ كسر شهادة الايداع
	 */
	public function getBreakDate()
	{
		return $this->break_date;
	}
	public function getBreakDateFormatted()
	{
		$breakDate = $this->break_date ;
		return $breakDate ? Carbon::make($breakDate)->format('d-m-Y'):null ;
	}

	/**
	 * * تاريخ استحقاق الايداع بس مش شرط يكون هو دا الفعلي لو التاريخ دا كان يوم جمعه مثلا فاهيكون اجازة
	 */
	public function getEndDate()
	{
		return $this->end_date;
	}
	/**
	 * * لما يتم تاكيد العمليه وقتها الفلوس الخاصة بالوديعه دي هتنزل علي انهي حساب ؟
	 */
	public function getMaturityAmountAddedToAccountId():int
	{
		return $this->maturity_amount_added_to_account_id ;
	}
	public function getMaturityAmountAddedToAccountNumber()
	{
		return $this->maturityAmountAddedToAccount ? $this->maturityAmountAddedToAccount->getAccountNumber() : null ;
	}
	public function maturityAmountAddedToAccount():BelongsTo
	{
		return $this->belongsTo(FinancialInstitutionAccount::class,'maturity_amount_added_to_account_id','id');
	}

	public function getEndDateFormatted()
	{
		$endDate = $this->getEndDate() ;
		return $endDate ? Carbon::make($endDate)->format('d-m-Y'):null ;
	}
	public function getAccountNumber()
	{
		return $this->account_number ;
	}

	public function getAmount()
	{
		return $this->amount ;
	}
	public function getAmountFormatted()
	{
		$amount = $this->getAmount();
		return number_format($amount) ;
	}

	public function getInterestRate()
	{
		return $this->interest_rate?:0;
	}

	public function getInterestRateFormatted()
	{
		return $this->getInterestRate() .' %';
	}

	public function getInterestAmount()
	{
		return $this->interest_amount?:0;
	}

	public function getInterestAmountFormatted()
	{
		$interestAmount = $this->getInterestAmount();
		return number_format($interestAmount,0);
	}

	public function getBreakInterestAmount()
	{
		return $this->break_interest_amount?:0;
	}

	public function getBreakInterestAmountFormatted()
	{
		return number_format($this->getBreakInterestAmount(),0);
	}
	public function getBreakChargeAmount()
	{
		return $this->break_charge_amount?:0;
	}

	public function getBreakChargeAmountFormatted()
	{
		return number_format($this->getBreakChargeAmount(),0);
	}

	public function getActualInterestAmount()
	{
		return $this->actual_interest_amount ?:0;
	}

	public function getActualInterestAmountFormatted()
	{
		return number_format($this->getActualInterestAmount(),0);
	}

	public function getCurrency()
	{
		return $this->currency ;
	}
	public function financialInstitution()
	{
		return $this->belongsTo(FinancialInstitution::class , 'financial_institution_id','id');
	}


	public function currentAccountDebitBankStatement()
	{
		return $this->hasOne(CurrentAccountBankStatement::class,'certificate_of_deposit_id','id')->where('is_debit',1);
	}
	public function currentAccountDebitBankStatements()
	{
		return $this->hasMany(CurrentAccountBankStatement::class,'certificate_of_deposit_id','id')->where('is_debit',1)->orderBy('full_date','desc');
	}


	public function currentAccountCreditBankStatement()
	{
		return $this->hasOne(CurrentAccountBankStatement::class,'certificate_of_deposit_id','id')->where('is_credit',1);
	}
	public function currentAccountCreditBankStatements()
	{
		return $this->hasMany(CurrentAccountBankStatement::class,'certificate_of_deposit_id','id')->where('is_credit',1)->orderBy('full_date','desc');
	}
	/**
	 * * علشان نجيب الاتنين مع بعض مرة واحدة
	 */
	public function currentAccountBankStatements()
	{
		return $this->hasMany(CurrentAccountBankStatement::class,'certificate_of_deposit_id','id')->orderBy('full_date','desc');
	}
	public function isDueTodayOrGreater()
	{
		$endDate = $this->getEndDate() ;
		return  $endDate && Carbon::make($endDate)->greaterThanOrEqualTo(now());
	}

    public static function getAllAccountNumberForCurrency($companyId , $currencyName,$financialInstitutionId,$keyName='account_number'):array
	{

		return self::where('company_id',$companyId)->where('currency',$currencyName)
		->where('financial_institution_id',$financialInstitutionId)
		->where('status',CertificatesOfDeposit::RUNNING)
		->pluck('account_number',$keyName)->toArray();
	}
	public static function findByAccountNumber( string $accountNumber,int $companyId)
	{
		return self::where('company_id',$companyId)->where('account_number',$accountNumber)->first();
	} 
	public function fullySecuredCleanOverdraft()
	{
		$cdAccount = AccountType::onlyCdAccounts()->first();
		return $this->hasOne(FullySecuredOverdraft::class,'cd_or_td_account_id','id')
		->where('cd_or_td_account_type_id',$cdAccount->id);
	}
	public function getType()
	{
		return __('Certificate Of Deposit');
	}
	public function getCurrencyFormatted()
	{
		return Str::upper($this->getCurrency());
	}
	public function getLastAmountFormatted()
	{
		return number_format($this->amount) ;
	}
	public function letterOfGuaranteeIssuance()
	{
		$cdAccount = AccountType::onlyCdAccounts()->first();
		return $this->hasOne(LetterOfGuaranteeIssuance::class,'cash_cover_deducted_from_account_id','id')
		->where('cash_cover_deducted_from_account_type',$cdAccount->id);
	}

public function getOdooCode()
	{
		return $this->odoo_code;
	}
	
	public function getOdooId():int 
	{
		if(is_null($this->odoo_id)){
			throw new \Exception('Odoo Code For Time Of Deposit ' . $this->getAccountNumber() . ' Not Found');
		}
		return $this->odoo_id;
	}
	public function getJournalId():?int 
	{
		return $this->journal_id ;
	}
	public function deleteOdooRelations($isBreakOrApplyDeposit)
	{
		$this->deleteOdoo($isBreakOrApplyDeposit);	
	}
	
}
