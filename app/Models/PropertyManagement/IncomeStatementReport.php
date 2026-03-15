<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $existing_loans_interests_expense
 * @property array<array-key, mixed>|null $fixed_asset_loan_interest_expenses
 * @property string|null $manpower_expenses
 * @property array<array-key, mixed>|null $total_manpower_expenses
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
 * @property array<array-key, mixed>|null $full_coverage_rent_revenues
 * @property array<array-key, mixed>|null $partial_coverage_rent_revenues
 * @property array<array-key, mixed>|null $to_be_delivered_rent_revenues
 * @property array<array-key, mixed>|null $property_forecasted_rent_revenues
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereCorporateTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereCorporateTaxesEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereCostOfService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereDepreciationExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereExistingLoansInterestsExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereFixedAssetLoanInterestExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereFullCoverageRentRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereGeneralExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereInterestCashSurplus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereManpowerExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereMarketingExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereOdaInterests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereOpeningDepreciationExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereOtherOperationExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport wherePartialCoverageRentRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport wherePropertyForecastedRentRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereSalesExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereToBeDeliveredRentRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereTotalCostOfService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereTotalGeneralExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereTotalManpowerExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereTotalMarketingExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereTotalOtherOperationExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereTotalSalesExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatementReport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IncomeStatementReport extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
				'interest_corridor_changes'=>'array',
				'loans_interest_corridor_changes'=>'array',
				'full_coverage_rent_revenues'=>'array',
				'partial_coverage_rent_revenues'=>'array',
				'to_be_delivered_rent_revenues'=>'array',
				'property_forecasted_rent_revenues'=>'array',
                // 'existing_ecl_expenses'=>'array',
                // 'non_performing_existing_ecl_expenses'=>'array',
                // 'existing_interests_expense'=>'array',
                'existing_loans_interests_expense'=>'array',
                // 'fixed_asset_loan_interest_expenses'=>'array',
                // 'securitization_early_settlement_expense'=>'array',
                // 'securitization_expense'=>'array',
                'interest_cash_surplus'=>'array',
                // 'securitization_gain_or_loss'=>'array',
                // 'existing_interests_revenues'=>'array',
                // 'securitization_reverse_interest_revenues'=>'array',
                // 'securitization_collection_revenues'=>'array',
                // 'leasing_revenue'=>'array',
                // 'direct-factoring_revenue'=>'array',
                // 'reverse-factoring_revenue'=>'array',
                // 'ijara_revenue'=>'array',
                // 'portfolio-mortgage_revenue'=>'array',
                // 'microfinance_revenue'=>'array',
                // 'consumer-finance_revenue'=>'array',
				
				// 'existing_interests_expense'=>'array',
            // 'existing_loans_interests_expense'=>'array',
            'fixed_asset_loan_interest_expenses'=>'array',
            // 'securitization_reverse_loan_interest_expense'=>'array',
            // 'securitization_early_settlement_expense'=>'array',
            // 'securitization_expense'=>'array',
			'corporate_taxes'=>'array',
            // 'leasing_bank_interest'=>'array',
            // 'leasing_monthly_ecl_expense'=>'array',
            // 'leasing_accumulated_ecl_expense'=>'array',
            // 'direct-factoring_bank_interest'=>'array',
            // 'direct-factoring_monthly_ecl_expense'=>'array',
            // 'direct-factoring_accumulated_ecl_expense'=>'array',
            // 'reverse-factoring_bank_interest'=>'array',
            // 'reverse-factoring_monthly_ecl_expense'=>'array',
            // 'reverse-factoring_accumulated_ecl_expense'=>'array',
            // 'total_admin_fees'=>'array',
            // 'ijara_bank_interest'=>'array',
            // 'ijara_monthly_ecl_expense'=>'array',
            // 'ijara_accumulated_ecl_expense'=>'array',
            // 'portfolio-mortgage_bank_interest'=>'array',
            // 'consumer-finance_bank_interest'=>'array',
            // 'microfinance_bank_interest'=>'array',
            'total_manpower_expenses'=>'array',
       
            // 'ecl_expenses'=>'array',
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
