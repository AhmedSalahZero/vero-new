<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

class CashflowStatementReport extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
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
                            , "securitization_npv"=>'array'
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
