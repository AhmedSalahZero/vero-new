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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
     * * الوديعه لاجل هي عباره عن مبلغ معين من المال بيتمجد لفتره محددة وبينزل عليه فؤائد
     * * وبيختلف عن الشهادة بان مدة بتكون اقل وبالتالي فايدة اقل
     * * يعني الوديعه بتكون من اسبوع لسنه مثلا اما الشهادة فا بتبدا من ثلاث سنين وانت طالع
     */
class TimeOfDeposit extends Model
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
    public function getDepositDateOrBreakDate():string
    {
        if ($this->isBroken()) {
            return $this->getBreakDate();
        }
        return $this->getDepositDate();
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
        return $this->belongsTo(FinancialInstitutionAccount::class, 'maturity_amount_added_to_account_id', 'id');
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
        return number_format($interestAmount, 0);
    }

    public function getBreakInterestAmount()
    {
        return $this->break_interest_amount?:0;
    }

    public function getBreakInterestAmountFormatted()
    {
        return number_format($this->getBreakInterestAmount(), 0);
    }
    public function getBreakChargeAmount()
    {
        return $this->break_charge_amount?:0;
    }

    public function getBreakChargeAmountFormatted()
    {
        return number_format($this->getBreakChargeAmount(), 0);
    }

    public function getActualInterestAmount()
    {
        return $this->actual_interest_amount ?:0;
    }

    public function getActualInterestAmountFormatted()
    {
        return number_format($this->getActualInterestAmount(), 0);
    }

    public function getCurrency()
    {
        return $this->currency ;
    }
    public function financialInstitution()
    {
        return $this->belongsTo(FinancialInstitution::class, 'financial_institution_id', 'id');
    }
    public function getFinancialInstitutionName():string
    {
        return $this->financialInstitution ? $this->financialInstitution->getName() : __('N/A');
    }
    public function currentAccountDebitBankStatement()
    {
        return $this->hasOne(CurrentAccountBankStatement::class, 'time_of_deposit_id', 'id')->where('is_debit', 1);
    }
    public function currentAccountDebitBankStatements()
    {
        return $this->hasMany(CurrentAccountBankStatement::class, 'time_of_deposit_id', 'id')->where('is_debit', 1)->orderBy('full_date', 'desc');
    }


    public function currentAccountCreditBankStatement()
    {
        return $this->hasOne(CurrentAccountBankStatement::class, 'time_of_deposit_id', 'id')->where('is_credit', 1);
    }
    public function currentAccountCreditBankStatements()
    {
        return $this->hasMany(CurrentAccountBankStatement::class, 'time_of_deposit_id', 'id')->where('is_credit', 1)->orderBy('full_date', 'desc');
    }
    /**
     * * علشان نجيب الاتنين مع بعض مرة واحدة
     */
    public function currentAccountBankStatements()
    {
        return $this->hasMany(CurrentAccountBankStatement::class, 'time_of_deposit_id', 'id')->orderBy('full_date', 'desc');
    }
    public function isDueTodayOrGreater()
    {
        $endDate = $this->getEndDate() ;
        return  $endDate && Carbon::make($endDate)->greaterThanOrEqualTo(now());
    }
    public static function getAllAccountNumberForCurrency($companyId, $currencyName, $financialInstitutionId, $keyName='account_number'):array
    {
        return self::where('company_id', $companyId)->where('currency', $currencyName)
        ->where('financial_institution_id', $financialInstitutionId)
        ->where('status', TimeOfDeposit::RUNNING)
        ->pluck('account_number', $keyName)->toArray();
    }
    
    public static function findByAccountNumber(string $accountNumber, int $companyId)
    {
        return self::where('company_id', $companyId)->where('account_number', $accountNumber)->first();
    }
    public function fullySecuredCleanOverdraft()
    {
        $tdAccount = AccountType::onlyTdAccounts()->first();
        return $this->hasOne(FullySecuredOverdraft::class, 'cd_or_td_account_id', 'id')
        ->where('cd_or_td_account_type_id', $tdAccount->id);
    }
    public function getType()
    {
        return __('Time Of Deposit');
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
        $tdAccount = AccountType::onlyTdAccounts()->first();
        return $this->hasOne(LetterOfGuaranteeIssuance::class, 'cash_cover_deducted_from_account_id', 'id')
        ->where('cash_cover_deducted_from_account_type', $tdAccount->id);
    }
    public function renewalDateHistories():HasMany
    {
        return $this->hasMany(TdRenewalDateHistory::class, 'time_of_deposit_id', 'id');
    }
    public function getRenewalDateBefore(string $date):string
    {
        return  $this->renewalDateHistories->where('renewal_date', '<', $date)->sortByDesc('renewal_date')->first()->renewal_date;
    }
    public function getRenewalDate()
    {
        return $this->getEndDate();
    }
    public function getDiffBetweenEndDateAndStartDate():int
    {
        return Carbon::make($this->getEndDate())->diffInDays(Carbon::make($this->getStartDate()));
    }
    public function isExpired():bool
    {
        return Carbon::make($this->getEndDate())->lessThanOrEqualTo(now());
    }
    public function renewalDebitCurrentAccount(string $date)
    {
        return $this->hasOne(CurrentAccountBankStatement::class, 'time_of_deposit_id', 'id')->where('is_debit', 1)->where('is_td_renewal', 1)->where('date', $date)->first();
    }
    public function calculateInterestAmount(string $expiryDate, string $renewalDate, $newInterestRate, float $interestAmount = null)
    {
        $diffBetweenTwoDatesInDays = Carbon::make($renewalDate)->diffInDays(Carbon::make($expiryDate));
        $amount = $this->getAmount();
        return  $newInterestRate / 100 / 365 *  $diffBetweenTwoDatesInDays * $amount;
    }
    public function storeRenewalDebitCurrentAccount(string $expiryDate, string $renewalDate, $newInterestRate, $commentEn, $commentAr)
    {
        $financialInstitution = $this->financialInstitution;
        // $accountType = AccountType::where('slug',AccountType::CURRENT_ACCOUNT)->first() ;
        $statementDate = $expiryDate ;
        
        $accountNumber= $this->getMaturityAmountAddedToAccountNumber() ;
        $financialInstitutionId = $financialInstitution->id ;
        $interestAmount = $this->calculateInterestAmount($expiryDate, $renewalDate, $newInterestRate);
        $financialInstitutionAccount = FinancialInstitutionAccount::findByAccountNumber($accountNumber, getCurrentCompanyId(), $financialInstitutionId);
        $this->storeCurrentAccountDebitBankStatement($statementDate, $interestAmount, $financialInstitutionAccount->id, true, $commentEn, $commentAr);
        return $interestAmount;
    }
    public static function getAmountAndInterestAtDates(array &$result, $foreignExchangeRates, $mainFunctionalCurrency, int $companyId, string $startDate, string $endDate, string $currentWeekYear)
    {
        $tdsTypes = [
            self::MATURED => __('Matured'),
            self::BROKEN=>__('Broken'),
            self::RUNNING => __('Running')
        ];
        // $mainType = 'lg';
        $mainType = 'customers';
        // $mainType = 'lg';
        // $x = "end_date between " . $endDate . ' AND ' . $startDate ;
        
        $rows = DB::table('time_of_deposits')
    ->where('time_of_deposits.company_id', $companyId)
    // ->where('currency', $currency)
    ->whereRaw("(CASE 
                    WHEN status = 'broken' THEN break_date 
                    WHEN status = 'matured' THEN deposit_date 
                    ELSE end_date 
                END) BETWEEN '{$startDate}' AND '{$endDate}'")
    ->groupByRaw('status, currency, end_date')
    ->selectRaw("
        status,
        currency,
        CASE 
            WHEN status = 'broken' THEN break_date 
            WHEN status = 'matured' THEN deposit_date 
            ELSE end_date 
        END AS date,
        SUM(CASE 
            WHEN status = 'matured' THEN amount + actual_interest_amount
            WHEN status = 'broken' THEN amount + break_interest_amount
            WHEN status = 'running' THEN amount + interest_amount
            ELSE 0 
        END) AS total_amount
    ")
        //    ->limit(1)
        //    ->
           ->get();
        
    
         
        $totalCashInFlowKey = __('Total Cash Inflow');
        $subType = __('Time Of Deposits');
        foreach ($rows as $row) {
            $tdCurrency = $row->currency;
            $depositDate = $row->date;
            // $depositDate = $row->deposit_date;
            $exchangeRate = ForeignExchangeRate::getExchangeRateAt($tdCurrency, $mainFunctionalCurrency, $depositDate, $companyId, $foreignExchangeRates);
            $currentStatus = $tdsTypes[$row->status] ;
            // $lgType = $lgsTypes[$row->status];
            $currentPaidAmount = $row->total_amount*$exchangeRate ;
            $result[$mainType][$subType][$currentStatus]['weeks'][$currentWeekYear] = isset($result[$mainType][$subType][$currentStatus]['weeks'][$currentWeekYear]) ? $result[$mainType][$subType][$currentStatus]['weeks'][$currentWeekYear] + $currentPaidAmount :  $currentPaidAmount;
            $result[$mainType][$subType][$currentStatus]['total'] = isset($result[$mainType][$subType][$currentStatus]['total']) ? $result[$mainType][$subType][$currentStatus]['total']  + $currentPaidAmount : $currentPaidAmount;
            $currentTotal = $currentPaidAmount;
            $result[$mainType][$subType]['total'][$currentWeekYear] = isset($result[$mainType][$subType]['total'][$currentWeekYear]) ? $result[$mainType][$subType]['total'][$currentWeekYear] +  $currentTotal : $currentTotal ;
            $result['customers'][$totalCashInFlowKey]['total'][$currentWeekYear] = isset($result['customers'][$totalCashInFlowKey]['total'][$currentWeekYear]) ? $result['customers'][$totalCashInFlowKey]['total'][$currentWeekYear] + $currentPaidAmount :$currentPaidAmount;

            // $result[$mainType][$subType]['total']['total_of_total'] = isset($result[$mainType][$subType]['total']['total_of_total']) ? $result[$mainType][$subType]['total']['total_of_total'] + $result[$mainType][$subType]['total'][$currentWeekYear] : $result[$mainType][$subType]['total'][$currentWeekYear];
            //	$totalCashInFlowArray[$currentWeekYear] = isset($totalCashInFlowArray[$currentWeekYear]) ? $totalCashInFlowArray[$currentWeekYear] +   $currentTotal : $currentTotal ;
        }
        
    
    }
    public function getOdooCode()
    {
        return $this->odoo_code;
    }
    public function getOdooId():int
    {
        if (is_null($this->odoo_id)) {
            throw new \Exception('Odoo Code For Time Of Deposit ' . $this->getAccountNumber() . ' Not Found');
        }
        return $this->odoo_id;
    }
    public function getJournalId():?int
    {
        return $this->journal_id ;
    }
    
    public function getExpiryDate()
    {
        /**
         * @var TimeOfDeposit $this
         */
        return $this->getRenewalDateBefore($this->getRenewalDate());
        
    }
    
    public function fullyIntegratedWithOdoo():bool
    {
        return !$this->hasOdooError() && count($this->getOdooReferenceNames()) ;
    }
    public function getOdooReferenceNames():array
    {
        $result = [];
        foreach ([
            'inbound_break_odoo_reference',
            'inbound_odoo_reference'
        ] as $referenceColumnName) {
            if ($this->{$referenceColumnName}) {
                $result[] = $this->{$referenceColumnName};
            }
        }
        $interestOdooReferences = $this->currentAccountBankStatements->pluck('interest_odoo_reference')->toArray();
        foreach ($interestOdooReferences as $interestOdooReference) {
            $result[] = $interestOdooReference;
        }
        return $result;
    }
    public function hasOdooError():bool
    {
        return !$this->synced_with_odoo && $this->odoo_error_message ;
    }
    
    public function deleteOdooRelations(bool $isBreakOrApplyDeposit)
    {
        $this->deleteOdoo($isBreakOrApplyDeposit);
    }
}
