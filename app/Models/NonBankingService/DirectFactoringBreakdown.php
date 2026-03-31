<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $category
 * @property numeric $margin_rate
 * @property array<array-key, mixed>|null $percentage_payload
 * @property array<array-key, mixed>|null $loan_amounts
 * @property string|null $monthly_loan_amounts
 * @property array<array-key, mixed>|null $disbursement_amounts
 * @property array<array-key, mixed>|null $beginning_balance
 * @property array<array-key, mixed>|null $interest_revenue
 * @property array<array-key, mixed>|null $unearned_interest
 * @property array<array-key, mixed>|null $end_balance
 * @property array<array-key, mixed>|null $net_funding_amounts
 * @property array<array-key, mixed>|null $statement_beginning_balance
 * @property array<array-key, mixed>|null $direct_factoring_amounts
 * @property array<array-key, mixed>|null $direct_factoring_settlements
 * @property array<array-key, mixed>|null $statement_end_balance
 * @property array<array-key, mixed>|null $bank_beginning_balance
 * @property array<array-key, mixed>|null $bank_loan_amounts
 * @property array<array-key, mixed>|null $bank_loan_settlements
 * @property array<array-key, mixed>|null $bank_interest_expense_payments
 * @property array<array-key, mixed>|null $bank_interest_expense
 * @property array<array-key, mixed>|null $bank_end_balance
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereBankBeginningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereBankEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereBankInterestExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereBankInterestExpensePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereBankLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereBankLoanSettlements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereBankTotalDues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereBeginningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereDirectFactoringAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereDirectFactoringSettlements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereDisbursementAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereInterestRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereMonthlyLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereNetFundingAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown wherePercentagePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereStatementBeginningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereStatementEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereUnearnedInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\DirectFactoringBreakdown whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DirectFactoringBreakdown extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts =[
		'statement_end_balance'=>'array',
		'direct_factoring_settlements'=>'array',
		'direct_factoring_amounts'=>'array',
		'statement_beginning_balance'=>'array',
		'net_funding_amounts'=>'array',
		'end_balance'=>'array',
		'unearned_interest'=>'array',
		'interest_revenue'=>'array',
		'beginning_balance'=>'array',
		'percentage_payload'=>'array',
		'loan_amounts'=>'array',
		'bank_beginning_balance'=>'array',
		'bank_loan_amounts'=>'array',
		'bank_loan_settlements'=>'array',
		'bank_interest_expense_payments'=>'array',
//		'bank_total_dues'=>'array',
		'bank_interest_expense'=>'array',
		'bank_end_balance'=>'array',
		'disbursement_amounts'=>'array',
	];
	
	public function getBankEndBalancePayload():array 
	{
		return (array)$this->bank_end_balance;
	}
	public function getBankEndBalanceAtYearIndex(int $yearIndex)
	{
		return $this->getBankEndBalancePayload()[$yearIndex] ?? 0  ; 
	}
	
	
	public function getBankInterestExpensePayload():array 
	{
		return (array)$this->bank_interest_expense;
	}
	public function getBankInterestExpenseAtYearIndex(int $yearIndex)
	{
		return $this->getBankInterestExpensePayload()[$yearIndex] ?? 0  ; 
	}
	
	
	// public function getBankTotalDuePayload():array 
	// {
	// 	return (array)$this->bank_total_dues;
	// }
	// public function getBankTotalDueAtYearIndex(int $yearIndex)
	// {
	// 	return $this->getBankTotalDuePayload()[$yearIndex] ?? 0  ; 
	// }
	
	public function getBankInterestExpensePaymentPayload():array 
	{
		return (array)$this->bank_interest_expense_payments;
	}
	public function getBankInterestExpensePaymentAtYearIndex(int $yearIndex)
	{
		return $this->getBankInterestExpensePaymentPayload()[$yearIndex] ?? 0  ; 
	}
	
	
	public function getBankLoanSettlementPayload():array 
	{
		return (array)$this->bank_loan_settlements;
	}
	public function getBankLoanSettlementAtYearIndex(int $yearIndex)
	{
		return $this->getBankLoanSettlementPayload()[$yearIndex] ?? 0  ; 
	}
	
	
	public function getBankLoanAmountPayload():array 
	{
		return (array)$this->bank_loan_amounts;
	}
	public function getBankLoanAmountAtYearIndex(int $yearIndex)
	{
		return $this->getBankLoanAmountPayload()[$yearIndex] ?? 0  ; 
	}
	
	
	public function getBankBeginningBalancePayload():array 
	{
		return (array)$this->bank_beginning_balance;
	}
	public function getBankBeginningBalanceAtYearIndex(int $yearIndex)
	{
		return $this->getBankBeginningBalancePayload()[$yearIndex] ?? 0  ; 
	}
	
	
	
	public function getStatementEndBalancePayload():array 
	{
		return (array)$this->statement_end_balance;
	}
	public function getStatementEndBalanceAtYearIndex(int $yearIndex)
	{
		return $this->getStatementEndBalancePayload()[$yearIndex] ?? 0  ; 
	}
	
	
	
	public function getDirectFactoringSettlementsPayload():array 
	{
		return (array)$this->direct_factoring_settlements;
	}
	public function getDirectFactoringSettlementsAtYearIndex(int $yearIndex)
	{
		return $this->getDirectFactoringSettlementsPayload()[$yearIndex] ?? 0  ; 
	}
	
	
	
	
	public function getDirectFactoringAmountsPayload():array 
	{
		return (array)$this->direct_factoring_amounts;
	}
	public function getDirectFactoringAmountsAtYearIndex(int $yearIndex)
	{
		return $this->getDirectFactoringAmountsPayload()[$yearIndex] ?? 0  ; 
	}
	
	
	
	public function getStatementBeginningBalancePayload():array 
	{
		return (array)$this->statement_beginning_balance;
	}
	public function getStatementBeginningBalanceAtYearIndex(int $yearIndex)
	{
		return $this->getStatementBeginningBalancePayload()[$yearIndex] ?? 0  ; 
	}
	
	
	
	
	public function getBeginningBalancePayload():array 
	{
		return (array)$this->beginning_balance;
	}
	public function getBeginningBalanceAtYearIndex(int $yearIndex)
	{
		return $this->getBeginningBalancePayload()[$yearIndex] ?? 0  ; 
	}
	
	public function getInterestRevenuePayload():array 
	{
		return (array)$this->interest_revenue;
	}
	public function getInterestRevenueAtYearIndex(int $yearIndex)
	{
		return $this->getInterestRevenuePayload()[$yearIndex] ?? 0  ; 
	}
	
	
	public function getUnearnedInterestPayload():array 
	{
		return (array)$this->unearned_interest;
	}
	public function getUnearnedInterestAtYearIndex(int $yearIndex)
	{
		return $this->getUnearnedInterestPayload()[$yearIndex] ?? 0  ; 
	}
	
	
	public function getEndBalancePayload():array 
	{
		return (array)$this->end_balance;
	}
	public function getEndBalanceAtYearIndex(int $yearIndex)
	{
		return $this->getEndBalancePayload()[$yearIndex] ?? 0  ; 
	}
	
	public function getNetFundingAmountsPayload():array 
	{
		return (array)$this->net_funding_amounts;
	}
	public function getNetFundingAmountsAtMonthIndex(int $monthIndex)
	{
		return $this->getNetFundingAmountsPayload()[$monthIndex] ?? 0  ; 
	}
	public function getPercentagePayload():array 
	{
		return (array)$this->percentage_payload;
	}
	public function getPercentageAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getPercentagePayload()[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getLoanAmountPayload():array 
	{
		return (array)$this->loan_amounts;
	}
	public function getLoanAmountPayloadAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getLoanAmountPayload()[$yearOrMonthIndex] ?? 0  ; 
	}
	// public function 
	public function getCategory():int
	{
		return $this->category;
	}
	// spread rate
	public function getMarginRate()
	{
		return $this->margin_rate?:0;
	}

	public function getForeignKeyName():string
	{
		return 'direct_breakdown_id';
	}	
	public function getCategoryColumnName():string 
	{
		return 'category';
	}
	public function getRevenueType():string 
	{
		return Study::DIRECT_FACTORING;
	}
	public static function  getRow(?self $directFactoringBreakdown,array $datesAsIndexes,array $categories)
	{
		return [
			'category'=>$directFactoringBreakdown? $directFactoringBreakdown->getCategory() : $categories[0]['id']  , // first one is the default one
			'margin_rate'=>$directFactoringBreakdown ? $directFactoringBreakdown->getMarginRate()  : 0 ,
			'percentage_payload'=>$directFactoringBreakdown ? $directFactoringBreakdown->getPercentagePayload() : array_fill_keys($datesAsIndexes,0),
			'loan_amounts'=>$directFactoringBreakdown ? $directFactoringBreakdown->getLoanAmountPayload()  : array_fill_keys($datesAsIndexes,0),
		];
	}
}
