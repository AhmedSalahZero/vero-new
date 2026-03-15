<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $total_admin_fees
 * @property array<array-key, mixed>|null $existing_interests_revenues
 * @property array<array-key, mixed>|null $existing_interests_expense
 * @property array<array-key, mixed>|null $existing_loans_interests_expense
 * @property array<array-key, mixed>|null $fixed_asset_loan_interest_expenses
 * @property array<array-key, mixed>|null $securitization_reverse_interest_revenues
 * @property array<array-key, mixed>|null $securitization_reverse_loan_interest_expense
 * @property array<array-key, mixed>|null $securitization_collection_revenues
 * @property array<array-key, mixed>|null $securitization_early_settlement_expense
 * @property array<array-key, mixed>|null $securitization_expense
 * @property array<array-key, mixed>|null $securitization_gain_or_loss
 * @property array<array-key, mixed>|null $leasing_revenue
 * @property array<array-key, mixed>|null $leasing_bank_interest
 * @property array<array-key, mixed>|null $direct-factoring_revenue
 * @property array<array-key, mixed>|null $direct-factoring_bank_interest
 * @property array<array-key, mixed>|null $reverse-factoring_revenue
 * @property array<array-key, mixed>|null $reverse-factoring_bank_interest
 * @property array<array-key, mixed>|null $ijara_revenue
 * @property array<array-key, mixed>|null $ijara_bank_interest
 * @property array<array-key, mixed>|null $portfolio-mortgage_revenue
 * @property array<array-key, mixed>|null $portfolio-mortgage_bank_interest
 * @property array<array-key, mixed>|null $microfinance_revenue
 * @property array<array-key, mixed>|null $consumer-finance_revenue
 * @property array<array-key, mixed>|null $microfinance_bank_interest
 * @property array<array-key, mixed>|null $consumer-finance_bank_interest
 * @property string|null $manpower_expenses
 * @property array<array-key, mixed>|null $total_manpower_expenses
 * @property array<array-key, mixed>|null $existing_ecl_expenses
 * @property array<array-key, mixed>|null $non_performing_existing_ecl_expenses
 * @property array<array-key, mixed>|null $ecl_expenses
 * @property array<array-key, mixed>|null $depreciation_expenses
 * @property array<array-key, mixed>|null $opening_depreciation_expenses
 * @property array<array-key, mixed>|null $oda_interests (DC2Type:json)
 * @property string|null $cost-of-service
 * @property array<array-key, mixed>|null $total_cost-of-service
 * @property string|null $marketing-expense
 * @property array<array-key, mixed>|null $total_marketing-expense
 * @property string|null $other-operation-expense
 * @property array<array-key, mixed>|null $total_other-operation-expense
 * @property string|null $sales-expense
 * @property array<array-key, mixed>|null $total_sales-expense
 * @property string|null $general-expense
 * @property array<array-key, mixed>|null $total_general-expense
 * @property array<array-key, mixed>|null $corporate_taxes
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $interest_cash_surplus
 * @property string|null $corporate_taxes_end_balance
 * @property array<array-key, mixed>|null $interest_corridor_changes
 * @property array<array-key, mixed>|null $loans_interest_corridor_changes
 * @property array<array-key, mixed>|null $right_of_user_amortization
 * @property array<array-key, mixed>|null $rent_interest
 * @property array<array-key, mixed>|null $new_branches_rent_interest
 * @property array<array-key, mixed>|null $new_branches_rent_amortization
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereConsumerFinanceBankInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereConsumerFinanceRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereCorporateTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereCorporateTaxesEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereCostOfService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereDepreciationExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereDirectFactoringBankInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereDirectFactoringRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereEclExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereExistingEclExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereExistingInterestsExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereExistingInterestsRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereExistingLoansInterestsExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereFixedAssetLoanInterestExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereGeneralExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereIjaraBankInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereIjaraRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereInterestCashSurplus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereInterestCorridorChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereLeasingBankInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereLeasingRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereLoansInterestCorridorChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereManpowerExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereMarketingExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereMicrofinanceBankInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereMicrofinanceRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereNewBranchesRentAmortization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereNewBranchesRentInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereNonPerformingExistingEclExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereOdaInterests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereOpeningDepreciationExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereOtherOperationExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport wherePortfolioMortgageBankInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport wherePortfolioMortgageRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereRentInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereReverseFactoringBankInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereReverseFactoringRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereRightOfUserAmortization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereSalesExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereSecuritizationCollectionRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereSecuritizationEarlySettlementExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereSecuritizationExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereSecuritizationGainOrLoss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereSecuritizationReverseInterestRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereSecuritizationReverseLoanInterestExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereTotalAdminFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereTotalCostOfService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereTotalGeneralExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereTotalManpowerExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereTotalMarketingExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereTotalOtherOperationExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereTotalSalesExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IncomeStatementReport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IncomeStatementReport extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
				'interest_corridor_changes'=>'array',
				'rent_interest'=>'array',
				'new_branches_rent_interest'=>'array',
				'new_branches_rent_amortization'=>'array',
				'loans_interest_corridor_changes'=>'array',
                'existing_ecl_expenses'=>'array',
                'non_performing_existing_ecl_expenses'=>'array',
				'right_of_user_amortization'=>'array',            
                'interest_cash_surplus'=>'array',
                'securitization_gain_or_loss'=>'array',
                'existing_interests_revenues'=>'array',
                'securitization_reverse_interest_revenues'=>'array',
                'securitization_collection_revenues'=>'array',
                'leasing_revenue'=>'array',
                'direct-factoring_revenue'=>'array',
                'reverse-factoring_revenue'=>'array',
                'ijara_revenue'=>'array',
                'portfolio-mortgage_revenue'=>'array',
                'microfinance_revenue'=>'array',
                'consumer-finance_revenue'=>'array',
				
				'existing_interests_expense'=>'array',
            'existing_loans_interests_expense'=>'array',
            'fixed_asset_loan_interest_expenses'=>'array',
            'securitization_reverse_loan_interest_expense'=>'array',
            'securitization_early_settlement_expense'=>'array',
            'securitization_expense'=>'array',
			'corporate_taxes'=>'array',
            'leasing_bank_interest'=>'array',
            // 'leasing_monthly_ecl_expense'=>'array',
            // 'leasing_accumulated_ecl_expense'=>'array',
            'direct-factoring_bank_interest'=>'array',
            // 'direct-factoring_monthly_ecl_expense'=>'array',
            // 'direct-factoring_accumulated_ecl_expense'=>'array',
            'reverse-factoring_bank_interest'=>'array',
            // 'reverse-factoring_monthly_ecl_expense'=>'array',
            // 'reverse-factoring_accumulated_ecl_expense'=>'array',
            'total_admin_fees'=>'array',
            'ijara_bank_interest'=>'array',
            // 'ijara_monthly_ecl_expense'=>'array',
            // 'ijara_accumulated_ecl_expense'=>'array',
            'portfolio-mortgage_bank_interest'=>'array',
            'consumer-finance_bank_interest'=>'array',
            'microfinance_bank_interest'=>'array',
            'total_manpower_expenses'=>'array',
       
            'ecl_expenses'=>'array',
            'depreciation_expenses'=>'array',
            'opening_depreciation_expenses'=>'array',
            'oda_interests'=>'array',
            'total_cost-of-service'=>'array',
            'total_marketing-expense'=>'array',
            'total_other-operation-expense'=>'array',
            'total_sales-expense'=>'array',
            'total_general-expense'=>'array'
			
    ];

}
