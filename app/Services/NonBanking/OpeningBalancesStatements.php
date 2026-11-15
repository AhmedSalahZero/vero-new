<?php

namespace App\Services\NonBanking;

use App\Helpers\HArr;
use App\Models\NonBankingService\LongTermLoanOpeningBalance;
use App\Models\NonBankingService\OtherCreditsOpeningBalance;
use App\Models\NonBankingService\OtherDebtorsOpeningBalance;
use App\Models\NonBankingService\OtherLongTermAssetsOpeningBalance;
use App\Models\NonBankingService\OtherLongTermLiabilitiesOpeningBalance;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * المرحلة 7: اللي صفحة الأرصدة الافتتاحية بتكتبه في تقارير التدفقات وقائمة الدخل بعد الحفظ (التحصيلات والمدفوعات
 * * الافتتاحية بالشهر ، والكاش الافتتاحي ، وفوايد القروض طويلة الأجل). اتنقل من OpeningBalancesController::store
 * * زي ما هو ، عشان أمر non-banking:recalculate يعمله لكل دراسة من غير الصفحة.
 */
final class OpeningBalancesStatements
{
    public static function refresh(Study $study): void
    {
            $longTermLoanOpeningBalanceInterests = [];
            $studyMonthsForViews = $study->getStudyDates();
            $studyMonthsForViews = array_slice($studyMonthsForViews, 0, $study->getViewStudyEndDateAsIndex()+1);
            $sumKeys = array_keys($studyMonthsForViews);
        
            $totalExistingLongTermLoansPayments = [];
            $study->longTermLoanOpeningBalances->each(function (LongTermLoanOpeningBalance $longTermLoanOpeningBalance) use (&$longTermLoanOpeningBalanceInterests, &$totalExistingLongTermLoansPayments, $sumKeys) {
                $currentInterest = $longTermLoanOpeningBalance->interests ;
                $currentInstallments = $longTermLoanOpeningBalance->installments ;
                $longTermLoanOpeningBalanceInterests = HArr::sumAtDates([$longTermLoanOpeningBalanceInterests,$currentInterest], $sumKeys);
                $totalExistingLongTermLoansPayments[$longTermLoanOpeningBalance->id] =  HArr::sumAtDates([$currentInstallments,$currentInterest], $sumKeys);
            });
       
        
            $totalOtherLongTermAssetOpeningBalances=[];
            $study->otherLongTermAssetsOpeningBalances->each(function (OtherLongTermAssetsOpeningBalance $otherLongTermAssetOpeningBalance) use (&$totalOtherLongTermAssetOpeningBalances) {
                $totalOtherLongTermAssetOpeningBalances[$otherLongTermAssetOpeningBalance->id] = (array)$otherLongTermAssetOpeningBalance->payload;
            }); 
    		// $totalLongTermInvestmentsOpeningBalances=[];
            // $study->longTermInvestmentsOpeningBalances->each(function (LongTermInvestmentsOpeningBalance $longTermInvestmentsOpeningBalance) use (&$totalLongTermInvestmentsOpeningBalances) {
            //     $totalLongTermInvestmentsOpeningBalances[$longTermInvestmentsOpeningBalance->id] = (array)$longTermInvestmentsOpeningBalance->payload;
            // });
        
            $existingOtherLongTermLiabilitiesPayment=[];
            $study->otherLongTermLiabilitiesOpeningBalances->each(function (OtherLongTermLiabilitiesOpeningBalance $otherLongTermLiability) use (&$existingOtherLongTermLiabilitiesPayment) {
                $existingOtherLongTermLiabilitiesPayment[$otherLongTermLiability->id] = $otherLongTermLiability->payload;
            });
        
            $totalExistingOtherDebtorsCollection=[];
            $study->otherDebtorsOpeningBalances->each(function (OtherDebtorsOpeningBalance $otherDebtorOpeningBalance) use (&$totalExistingOtherDebtorsCollection) {
                $totalExistingOtherDebtorsCollection[$otherDebtorOpeningBalance->id] = $otherDebtorOpeningBalance->payload;
            });
        
		
        
            $totalExistingOtherCreditorsPayments=[];
            $study->otherCreditorsOpeningBalances->each(function (OtherCreditsOpeningBalance $otherCreditorOpeningBalance) use (&$totalExistingOtherCreditorsPayments) {
                $totalExistingOtherCreditorsPayments[$otherCreditorOpeningBalance->id] = $otherCreditorOpeningBalance->payload;
            });
    
            $openingBalance = $study->cashAndBankOpeningBalances->first() ;
            $openingCashAmount = $openingBalance ? $openingBalance->cash_and_bank_amount : 0;
            $totalExistingPortfolioInterest = $openingBalance ? $openingBalance->interests : [];
            $totalExistingPortfolioPrinciple = $openingBalance ? $openingBalance->payload : [];
    		$totalExistingPortfolioNonPerformingInterest = $openingBalance ? $openingBalance->non_performing_interests : [];
            $totalExistingPortfolioNonPerformingPrinciple = $openingBalance ? $openingBalance->non_performing_payload : [];
            $totalExistingPortfolioCollection = HArr::sumAtDates([$totalExistingPortfolioInterest ,$totalExistingPortfolioPrinciple,$totalExistingPortfolioNonPerformingInterest,$totalExistingPortfolioNonPerformingPrinciple ], $sumKeys);
        
        
            $supplierPayableOpeningBalance = $study->supplierPayableOpeningBalances->first() ;
            $totalExistingPortfolioLoansInterest = $supplierPayableOpeningBalance ? $supplierPayableOpeningBalance->portfolio_interest_expenses : [];
            $totalExistingPortfolioLoansPrinciple = $supplierPayableOpeningBalance ? $supplierPayableOpeningBalance->payload : [];
            $totalExistingPortfolioLoansPayments = HArr::sumAtDates([$totalExistingPortfolioLoansInterest ,$totalExistingPortfolioLoansPrinciple ], $sumKeys);
        
        
        
        
            DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id', $study->id)->update([
                        'other_long_term_asset_collections'=>json_encode($totalOtherLongTermAssetOpeningBalances),
                        'total_other_long_term_asset_collections'=>json_encode(HArr::sumAtDates(array_values($totalOtherLongTermAssetOpeningBalances), $sumKeys)),
			
                        'opening_cash'=>$openingCashAmount,
                        'existing_portfolio_collection'=>$totalExistingPortfolioCollection,
                        'existing_other_debtors_collection'=>$totalExistingOtherDebtorsCollection,
                        'total_existing_other_debtors_collection'=>json_encode(HArr::sumAtDates(array_values($totalExistingOtherDebtorsCollection), $sumKeys)),
                        'existing_portfolio_loans_payment'=>$totalExistingPortfolioLoansPayments,
                        'existing_other_creditors_payment'=>$totalExistingOtherCreditorsPayments,
                        'total_existing_other_creditors_payment'=>json_encode(HArr::sumAtDates(array_values($totalExistingOtherCreditorsPayments), $sumKeys)),
                        'existing_long_term_loans_payment'=>$totalExistingLongTermLoansPayments,
                        'total_existing_long_term_loans_payment'=>json_encode(HArr::sumAtDates(array_values($totalExistingLongTermLoansPayments), $sumKeys)),
                        'existing_other_long_term_liabilities_payment'=>$existingOtherLongTermLiabilitiesPayment,
                        'total_existing_other_long_term_liabilities_payment'=>json_encode(HArr::sumAtDates(array_values($existingOtherLongTermLiabilitiesPayment), $sumKeys))
            ]);
		
            DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')->where('study_id', $study->id)->update([
                    'existing_loans_interests_expense'=>json_encode($longTermLoanOpeningBalanceInterests)
            ]);
    }
}
