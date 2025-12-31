<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

class IncomeStatementReport extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
                'existing_ecl_expenses'=>'array',
                'existing_interests_expense'=>'array',
                'existing_loans_interests_expense'=>'array',
                'fixed_asset_loan_interest_expenses'=>'array',
                'securitization_early_settlement_expense'=>'array',
                'securitization_expense'=>'array',
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
            'existing_ecl_expenses'=>'array',
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
