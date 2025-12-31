<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

class BalanceSheet extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
    protected $guarded = ['id'];
	protected $table = 'balance_sheets';
    protected $casts = [
			'monthly_non_currency_assets'=>'array',
			'total_non_currency_assets'=>'array',
			'monthly_fixed_assets'=>'array',
			'yearly_fixed_assets'=>'array',
			'monthly_other_long_term_assets'=>'array',
			'yearly_other_long_term_assets'=>'array',
			'monthly_current_assets'=>'array',
			'total_current_assets'=>'array',
			'monthly_cash_and_banks'=>'array',
			'yearly_cash_and_banks'=>'array',
			'monthly_customer_outstanding'=>'array',
			'yearly_customer_outstanding'=>'array',
			'monthly_other_debtors'=>'array',
			'yearly_other_debtors'=>'array',
			'monthly_total_assets'=>'array',
			'yearly_total_assets'=>'array',
			'monthly_current_liabilities'=>'array',
			'yearly_current_liabilities'=>'array',
			'monthly_portfolio_loan_outstanding'=>'array',
			'yearly_portfolio_loan_outstanding'=>'array',
			'monthly_other_creditors'=>'array',
			'yearly_other_creditors'=>'array',
			'monthly_long_term_liabilities'=>'array',
			'yearly_long_term_liabilities'=>'array',
			'monthly_shareholder_equity'=>'array',
			'yearly_shareholder_equity'=>'array',
			'mtls_structures'=>'array',
    ];

}
