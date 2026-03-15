<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $total_admin_fees
 * @property array<array-key, mixed>|null $opening_dividend_payments
 * @property int|null $opening_cash
 * @property array<array-key, mixed>|null $other_long_term_asset_collections
 * @property array<array-key, mixed>|null $total_other_long_term_asset_collections
 * @property array<array-key, mixed>|null $ffe_loan_withdrawal
 * @property array<array-key, mixed>|null $existing_other_debtors_collection
 * @property array<array-key, mixed>|null $total_existing_other_debtors_collection
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
 * @property array<array-key, mixed>|null $full_coverage_rent_collections
 * @property array<array-key, mixed>|null $partial_coverage_rent_collections
 * @property array<array-key, mixed>|null $to_be_delivered_rent_collections
 * @property array<array-key, mixed>|null $property_forecasted_rent_collections
 * @property string|null $existing_properties_installments
 * @property string|null $new_properties_installments
 * @property array<array-key, mixed>|null $existing_portfolio_loans_payment
 * @property array<array-key, mixed>|null $existing_portfolio_collection
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereCashAndBankBeginningBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereCashEndBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereCashOpeningBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereCorporateTaxesEndBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereCorporateTaxesPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExistingLongTermLoansPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExistingOtherCreditorsPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExistingOtherDebtorsCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExistingOtherLongTermLiabilitiesPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExistingPortfolioCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExistingPortfolioLoansPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExistingPropertiesInstallments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExpensePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereExtraCapitalInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereFfeLoanWithdrawal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereFixedAssetLoanSchedulePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereFixedAssetPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereFullCoverageRentCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereHasManualEquityInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereManualCapitalInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereManualEquityInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereNetCashBeforeExtraCapitalInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereNewPropertiesInstallments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereOdaStatements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereOpeningCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereOpeningDividendPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereOtherLongTermAssetCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport wherePartialCoverageRentCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport wherePropertyForecastedRentCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereSalaryPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereSalaryTaxSocialInsurancePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereToBeDeliveredRentCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereTotalAdminFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereTotalExistingLongTermLoansPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereTotalExistingOtherCreditorsPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereTotalExistingOtherDebtorsCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereTotalExistingOtherLongTermLiabilitiesPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereTotalExpensePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereTotalFixedAssetReplacementCosts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereTotalOtherLongTermAssetCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashflowStatementReport whereWithholdPayments($value)
 * @mixin \Eloquent
 */
class CashflowStatementReport extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
		'full_coverage_rent_collections'=>'array',
		'partial_coverage_rent_collections'=>'array',
		'to_be_delivered_rent_collections'=>'array',
		'property_forecasted_rent_collections'=>'array',
		'interest_corridor_changes'=>'array',
		'loans_interest_corridor_changes'=>'array',
		'opening_dividend_payments'=>'array',
		'net_cash_before_extra_capital_injection'=>'array',
        // 'total_admin_fees'=>'array',
        'manual_equity_injection'=>'array',
                                'total_expense_payments'=>'array',
                                'total_other_long_term_asset_collections'=>'array',
                                'total_existing_long_term_loans_payment'=>'array',
                                'total_existing_other_creditors_payment'=>'array',
                                'total_existing_other_long_term_liabilities_payment'=>'array',
                                'total_existing_other_debtors_collection'=>'array',
                             "corporate_taxes_payments"=>'array',
                             "corporate_taxes_end_balances"=>'array',
                             "existing_long_term_loans_payment"=>'array'
                            , "existing_other_creditors_payment"=>'array'
                            , "existing_other_debtors_collection"=>'array'
                            , "existing_other_long_term_liabilities_payment"=>'array'
                            , "existing_portfolio_collection"=>'array'
                            , "existing_portfolio_loans_payment"=>'array'
                            , "expense_payments"=>'array'
                            , "ffe_loan_withdrawal"=>'array'
                            , "fixed_asset_loan_schedule_payments"=>'array'
                            , "fixed_asset_payments"=>'array'
                            // , "ijara_collection"=>'array'
                            // , "ijara_loan_withdrawal_amount"=>'array'
                            // , "ijara_payment"=>'array'
                            // , "leasing_collection"=>'array'
                            // , "leasing_loan_withdrawal_amount"=>'array'
                            // , "leasing_payment"=>'array'
                            // , "microfinance_collection"=>'array'
                            // , "consumer-finance_collection"=>'array'
                            // , "microfinance_loan_withdrawal_amount"=>'array'
                            // , "microfinance_payment"=>'array'
                            // , "consumer-finance_payment"=>'array'
                            // , "microfinance_oda_withdrawals"=>'array'
                            // , "consumer-finance_oda_withdrawals"=>'array'
                            // , "oda_withdrawals"=>'array' // total_oda_withdrawals
                            , "other_long_term_asset_collections"=>'array'
                            // , "portfolio-mortgage_collection"=>'array'
                            // , "portfolio-mortgage_loan_withdrawal_amount"=>'array'
                            // , "consumer-finance_loan_withdrawal_amount"=>'array'
                            // , "portfolio-mortgage_payment"=>'array'
                            // , "reverse-factoring_collection"=>'array'
                            // , "reverse-factoring_loan_withdrawal_amount"=>'array'
                            // , "reverse-factoring_payment"=>'array'
                            , "salary_payments"=>'array'
                            , "salary_tax_social_insurance_payments"=>'array'
                            // , "securitization_collection_revenues"=>'array'
                            // , "securitization_npv"=>'array'
                            // , "securitization_reverse_collection"=>'array'
                            // , "securitization_reverse_loan_payment"=>'array'
                            , "withhold_payments"=>'array'
                            // , "securitization_npv"=>'array'
                            // , "securitization_bank_settlement"=>'array'
                            // , "securitization_early_settlement_expense"=>'array'
							// , "securitization_expense"=>'array'
							, 'total_fixed_asset_replacement_costs'=>'array',
                             'oda_statements'=>'array',
                             'extra_capital_injection'=>'array',
                             'manual_capital_injection'=>'array',
                             'cash_end_balances'=>'array',
                             'cash_opening_balances'=>'array',
                             'cash_and_bank_beginning_balances'=>'array',
                            //  'leasing_disbursements'=>'array',
                            //  'direct-factoring_disbursements'=>'array',
                            //  'ijara_disbursements'=>'array',
                            //  'reverse-factoring_disbursements'=>'array',
                            //  'portfolio-mortgage_disbursements'=>'array',
                            //  'microfinance_disbursements'=>'array',
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
