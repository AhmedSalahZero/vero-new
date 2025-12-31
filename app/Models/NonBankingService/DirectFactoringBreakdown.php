<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  DirectFactoringBreakdown extends Model
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
		'bank_total_dues'=>'array',
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
	
	
	public function getBankTotalDuePayload():array 
	{
		return (array)$this->bank_total_dues;
	}
	public function getBankTotalDueAtYearIndex(int $yearIndex)
	{
		return $this->getBankTotalDuePayload()[$yearIndex] ?? 0  ; 
	}
	
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
	
}
