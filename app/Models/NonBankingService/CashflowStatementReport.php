<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $total_admin_fees
 * @property array<array-key, mixed>|null $opening_dividend_payments
 * @property array<array-key, mixed>|null $microfinance_disbursements
 * @property array<array-key, mixed>|null $portfolio-mortgage_disbursements
 * @property array<array-key, mixed>|null $reverse-factoring_disbursements
 * @property array<array-key, mixed>|null $ijara_disbursements
 * @property array<array-key, mixed>|null $direct-factoring_disbursements
 * @property array<array-key, mixed>|null $leasing_disbursements
 * @property int|null $opening_cash
 * @property array<array-key, mixed>|null $other_long_term_asset_collections
 * @property array<array-key, mixed>|null $total_other_long_term_asset_collections
 * @property array<array-key, mixed>|null $ffe_loan_withdrawal
 * @property array<array-key, mixed>|null $securitization_reverse_collection
 * @property array<array-key, mixed>|null $securitization_collection_revenues
 * @property array<array-key, mixed>|null $securitization_reverse_loan_payment
 * @property array<array-key, mixed>|null $securitization_npv
 * @property array<array-key, mixed>|null $securitization_bank_settlement
 * @property array<array-key, mixed>|null $securitization_early_settlement_expense
 * @property array<array-key, mixed>|null $securitization_expense
 * @property array<array-key, mixed>|null $leasing_collection
 * @property array<array-key, mixed>|null $leasing_loan_withdrawal_amount
 * @property array<array-key, mixed>|null $leasing_payment
 * @property array<array-key, mixed>|null $direct-factoring_collection
 * @property array<array-key, mixed>|null $direct-factoring_loan_withdrawal_amount
 * @property array<array-key, mixed>|null $direct-factoring_payment
 * @property array<array-key, mixed>|null $direct-factoring_bank_interest
 * @property array<array-key, mixed>|null $reverse-factoring_collection
 * @property array<array-key, mixed>|null $reverse-factoring_loan_withdrawal_amount
 * @property array<array-key, mixed>|null $reverse-factoring_payment
 * @property array<array-key, mixed>|null $ijara_collection
 * @property array<array-key, mixed>|null $ijara_loan_withdrawal_amount
 * @property array<array-key, mixed>|null $ijara_payment
 * @property array<array-key, mixed>|null $portfolio-mortgage_collection
 * @property array<array-key, mixed>|null $portfolio-mortgage_loan_withdrawal_amount
 * @property array<array-key, mixed>|null $consumer-finance_loan_withdrawal_amount
 * @property array<array-key, mixed>|null $portfolio-mortgage_payment
 * @property array<array-key, mixed>|null $microfinance_collection
 * @property array<array-key, mixed>|null $consumer-finance_collection
 * @property array<array-key, mixed>|null $microfinance_loan_withdrawal_amount
 * @property array<array-key, mixed>|null $microfinance_payment
 * @property array<array-key, mixed>|null $consumer-finance_payment
 * @property array<array-key, mixed>|null $existing_portfolio_collection
 * @property array<array-key, mixed>|null $existing_other_debtors_collection
 * @property array<array-key, mixed>|null $total_existing_other_debtors_collection
 * @property array<array-key, mixed>|null $existing_portfolio_loans_payment
 * @property array<array-key, mixed>|null $existing_other_creditors_payment
 * @property array<array-key, mixed>|null $total_existing_other_creditors_payment
 * @property array<array-key, mixed>|null $existing_long_term_loans_payment
 * @property array<array-key, mixed>|null $total_existing_long_term_loans_payment
 * @property array<array-key, mixed>|null $existing_other_long_term_liabilities_payment
 * @property array<array-key, mixed>|null $total_existing_other_long_term_liabilities_payment
 * @property array<array-key, mixed>|null $fixed_asset_loan_schedule_payments
 * @property array<array-key, mixed>|null $fixed_asset_payments
 * @property array<array-key, mixed>|null $expense_payments
 * @property array<array-key, mixed>|null $total_expense_payments
 * @property array<array-key, mixed>|null $salary_payments
 * @property array<array-key, mixed>|null $salary_tax_social_insurance_payments
 * @property array<array-key, mixed>|null $withhold_payments
 * @property array<array-key, mixed>|null $consumer-finance_oda_withdrawals
 * @property array<array-key, mixed>|null $microfinance_oda_withdrawals
 * @property array<array-key, mixed>|null $oda_withdrawals
 * @property array<array-key, mixed>|null $corporate_taxes_payments
 * @property array<array-key, mixed>|null $corporate_taxes_end_balances
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $total_fixed_asset_replacement_costs
 * @property array<array-key, mixed>|null $oda_statements
 * @property array<array-key, mixed>|null $extra_capital_injection
 * @property array<array-key, mixed>|null $manual_capital_injection
 * @property array<array-key, mixed>|null $cash_opening_balances
 * @property array<array-key, mixed>|null $cash_and_bank_beginning_balances
 * @property array<array-key, mixed>|null $cash_end_balances
 * @property int $has_manual_equity_injection
 * @property array<array-key, mixed>|null $manual_equity_injection
 * @property array<array-key, mixed>|null $net_cash_before_extra_capital_injection
 * @property string|null $leasing_portfolio_interest_change_collections
 * @property string|null $leasing_bank_portfolio_interest_change_payments
 * @property array<array-key, mixed>|null $interest_corridor_changes
 * @property array<array-key, mixed>|null $loans_interest_corridor_changes
 * @property array<array-key, mixed>|null $rent_payment
 * @property array<array-key, mixed>|null $new_branches_rent_payments
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereCashAndBankBeginningBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereCashEndBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereCashOpeningBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereConsumerFinanceCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereConsumerFinanceLoanWithdrawalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereConsumerFinanceOdaWithdrawals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereConsumerFinancePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereCorporateTaxesEndBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereCorporateTaxesPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereDirectFactoringBankInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereDirectFactoringCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereDirectFactoringDisbursements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereDirectFactoringLoanWithdrawalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereDirectFactoringPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereExistingLongTermLoansPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereExistingOtherCreditorsPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereExistingOtherDebtorsCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereExistingOtherLongTermLiabilitiesPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereExistingPortfolioCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereExistingPortfolioLoansPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereExpensePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereExtraCapitalInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereFfeLoanWithdrawal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereFixedAssetLoanSchedulePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereFixedAssetPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereHasManualEquityInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereIjaraCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereIjaraDisbursements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereIjaraLoanWithdrawalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereIjaraPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereInterestCorridorChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereLeasingBankPortfolioInterestChangePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereLeasingCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereLeasingDisbursements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereLeasingLoanWithdrawalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereLeasingPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereLeasingPortfolioInterestChangeCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereLoansInterestCorridorChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereManualCapitalInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereManualEquityInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereMicrofinanceCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereMicrofinanceDisbursements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereMicrofinanceLoanWithdrawalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereMicrofinanceOdaWithdrawals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereMicrofinancePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereNetCashBeforeExtraCapitalInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereNewBranchesRentPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereOdaStatements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereOdaWithdrawals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereOpeningCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereOpeningDividendPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereOtherLongTermAssetCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport wherePortfolioMortgageCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport wherePortfolioMortgageDisbursements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport wherePortfolioMortgageLoanWithdrawalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport wherePortfolioMortgagePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereRentPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereReverseFactoringCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereReverseFactoringDisbursements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereReverseFactoringLoanWithdrawalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereReverseFactoringPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSalaryPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSalaryTaxSocialInsurancePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSecuritizationBankSettlement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSecuritizationCollectionRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSecuritizationEarlySettlementExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSecuritizationExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSecuritizationNpv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSecuritizationReverseCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereSecuritizationReverseLoanPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereTotalAdminFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereTotalExistingLongTermLoansPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereTotalExistingOtherCreditorsPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereTotalExistingOtherDebtorsCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereTotalExistingOtherLongTermLiabilitiesPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereTotalExpensePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereTotalFixedAssetReplacementCosts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereTotalOtherLongTermAssetCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\CashflowStatementReport whereWithholdPayments($value)
 * @mixin \Eloquent
 */
class CashflowStatementReport extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
		'new_branches_rent_payments'=>'array',
		'interest_corridor_changes'=>'array',
		'loans_interest_corridor_changes'=>'array',
		'opening_dividend_payments'=>'array',
		'rent_payment'=>'array',
		'net_cash_before_extra_capital_injection'=>'array',
        'total_admin_fees'=>'array',
        'manual_equity_injection'=>'array',
                                'total_expense_payments'=>'array',
                                'total_other_long_term_asset_collections'=>'array',
                                'total_existing_long_term_loans_payment'=>'array',
                                'total_existing_other_creditors_payment'=>'array',
                                'total_existing_other_long_term_liabilities_payment'=>'array',
                                'total_existing_other_debtors_collection'=>'array',
                             "corporate_taxes_payments"=>'array',
                             "corporate_taxes_end_balances"=>'array'
                            , "direct-factoring_collection"=>'array'
                            , "direct-factoring_loan_withdrawal_amount"=>'array'
                            , "direct-factoring_payment"=>'array',
                            'direct-factoring_bank_interest'=>'array'
                            , "existing_long_term_loans_payment"=>'array'
                            , "existing_other_creditors_payment"=>'array'
                            , "existing_other_debtors_collection"=>'array'
                            , "existing_other_long_term_liabilities_payment"=>'array'
                            , "existing_portfolio_collection"=>'array'
                            , "existing_portfolio_loans_payment"=>'array'
                            , "expense_payments"=>'array'
                            , "ffe_loan_withdrawal"=>'array'
                            , "fixed_asset_loan_schedule_payments"=>'array'
                            , "fixed_asset_payments"=>'array'
                            , "ijara_collection"=>'array'
                            , "ijara_loan_withdrawal_amount"=>'array'
                            , "ijara_payment"=>'array'
                            , "leasing_collection"=>'array'
                            , "leasing_loan_withdrawal_amount"=>'array'
                            , "leasing_payment"=>'array'
                            , "microfinance_collection"=>'array'
                            , "consumer-finance_collection"=>'array'
                            , "microfinance_loan_withdrawal_amount"=>'array'
                            , "microfinance_payment"=>'array'
                            , "consumer-finance_payment"=>'array'
                            , "microfinance_oda_withdrawals"=>'array'
                            , "consumer-finance_oda_withdrawals"=>'array'
                            , "oda_withdrawals"=>'array' // total_oda_withdrawals
                            , "other_long_term_asset_collections"=>'array'
                            , "portfolio-mortgage_collection"=>'array'
                            , "portfolio-mortgage_loan_withdrawal_amount"=>'array'
                            , "consumer-finance_loan_withdrawal_amount"=>'array'
                            , "portfolio-mortgage_payment"=>'array'
                            , "reverse-factoring_collection"=>'array'
                            , "reverse-factoring_loan_withdrawal_amount"=>'array'
                            , "reverse-factoring_payment"=>'array'
                            , "salary_payments"=>'array'
                            , "salary_tax_social_insurance_payments"=>'array'
                            , "securitization_collection_revenues"=>'array'
                            , "securitization_npv"=>'array'
                            , "securitization_reverse_collection"=>'array'
                            , "securitization_reverse_loan_payment"=>'array'
                            , "withhold_payments"=>'array'
                            , "securitization_bank_settlement"=>'array'
                            , "securitization_early_settlement_expense"=>'array',
                             "securitization_expense"=>'array',
                             'total_fixed_asset_replacement_costs'=>'array',
                             'oda_statements'=>'array',
                             'extra_capital_injection'=>'array',
                             'manual_capital_injection'=>'array',
                             'cash_end_balances'=>'array',
                             'cash_opening_balances'=>'array',
                             'cash_and_bank_beginning_balances'=>'array',
                             'leasing_disbursements'=>'array',
                             'direct-factoring_disbursements'=>'array',
                             'ijara_disbursements'=>'array',
                             'reverse-factoring_disbursements'=>'array',
                             'portfolio-mortgage_disbursements'=>'array',
                             'microfinance_disbursements'=>'array',
    ];
	public function hasManualEquityInjection():bool
	{
		return (bool)$this->has_manual_equity_injection;
	}
	public function getManualEquityInjectionAtMonthIndex(int $monthIndex)
	{
		return $this->manual_equity_injection[$monthIndex]??0;
	}
	
}
