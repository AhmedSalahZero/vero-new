<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $monthly_non_currency_assets
 * @property array<array-key, mixed>|null $total_non_currency_assets
 * @property array<array-key, mixed>|null $monthly_fixed_assets
 * @property array<array-key, mixed>|null $yearly_fixed_assets
 * @property array<array-key, mixed>|null $monthly_other_long_term_assets
 * @property array<array-key, mixed>|null $yearly_other_long_term_assets
 * @property array<array-key, mixed>|null $monthly_current_assets
 * @property array<array-key, mixed>|null $total_current_assets
 * @property array<array-key, mixed>|null $monthly_cash_and_banks
 * @property array<array-key, mixed>|null $yearly_cash_and_banks
 * @property array<array-key, mixed>|null $monthly_customer_outstanding
 * @property array<array-key, mixed>|null $yearly_customer_outstanding
 * @property array<array-key, mixed>|null $monthly_other_debtors
 * @property array<array-key, mixed>|null $yearly_other_debtors
 * @property array<array-key, mixed>|null $monthly_total_assets
 * @property array<array-key, mixed>|null $yearly_total_assets
 * @property array<array-key, mixed>|null $monthly_current_liabilities
 * @property array<array-key, mixed>|null $yearly_current_liabilities
 * @property array<array-key, mixed>|null $monthly_portfolio_loan_outstanding
 * @property array<array-key, mixed>|null $yearly_portfolio_loan_outstanding
 * @property array<array-key, mixed>|null $monthly_other_creditors
 * @property array<array-key, mixed>|null $yearly_other_creditors
 * @property array<array-key, mixed>|null $monthly_long_term_liabilities
 * @property array<array-key, mixed>|null $yearly_long_term_liabilities
 * @property array<array-key, mixed>|null $monthly_shareholder_equity
 * @property array<array-key, mixed>|null $yearly_shareholder_equity
 * @property array<array-key, mixed>|null $mtls_structures
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $monthly_long_term_investments
 * @property array<array-key, mixed>|null $yearly_long_term_investments
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyCashAndBanks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyCurrentAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyCurrentLiabilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyCustomerOutstanding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyFixedAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyLongTermInvestments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyLongTermLiabilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyNonCurrencyAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyOtherCreditors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyOtherDebtors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyOtherLongTermAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyPortfolioLoanOutstanding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyShareholderEquity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMonthlyTotalAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereMtlsStructures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereTotalCurrentAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereTotalNonCurrencyAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyCashAndBanks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyCurrentLiabilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyCustomerOutstanding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyFixedAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyLongTermInvestments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyLongTermLiabilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyOtherCreditors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyOtherDebtors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyOtherLongTermAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyPortfolioLoanOutstanding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyShareholderEquity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\BalanceSheet whereYearlyTotalAssets($value)
 * @mixin \Eloquent
 */
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
			'monthly_long_term_investments'=>'array',
			'yearly_long_term_investments'=>'array',
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
