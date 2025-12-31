<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HArr;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpeningBalancesRequest;
use App\Models\Company;
use App\Models\NonBankingService\LongTermLoanOpeningBalance;
use App\Models\NonBankingService\OtherCreditsOpeningBalance;
use App\Models\NonBankingService\OtherDebtorsOpeningBalance;
use App\Models\NonBankingService\OtherLongTermAssetsOpeningBalance;
use App\Models\NonBankingService\OtherLongTermLiabilitiesOpeningBalance;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpeningBalancesController extends Controller
{
    use NonBankingService ;
    
    public function create(Company $company, Request $request, Study $study)
    {
        return view('non_banking_services.openingBalances.form', array_merge($study->getOpeningBalancesViewVars(), ['inEditMode'=>false]));
    }
    
    public function store(Company $company, StoreOpeningBalancesRequest $request, Study $study)
    {
		
        $study->storeRepeaterRelations($request, ['fixedAssetOpeningBalances','cashAndBankOpeningBalances','otherDebtorsOpeningBalances','vatAndCreditWithholdTaxesOpeningBalances'
        ,'supplierPayableOpeningBalances','otherCreditorsOpeningBalances','otherLongTermAssetsOpeningBalances','otherLongTermLiabilitiesOpeningBalances','equityOpeningBalances','longTermLoanOpeningBalances'
         ], $company, ['study_id'=>$study->id]);
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
        $totalExistingPortfolioCollection = HArr::sumAtDates([$totalExistingPortfolioInterest ,$totalExistingPortfolioPrinciple ], $sumKeys);
        
        
        
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
		$checkSettlementsValidations = $this->checkSettlementsOrPrincipleValidation($request);
		if(!$checkSettlementsValidations['status']){
			return redirect()->back()->with('fail',$checkSettlementsValidations['message']);
		}
		if($request->get('total_liabilities_and_equity_minus_total_assets') != 0 ){
			return redirect()->back()->with('fail',__('Total Assets Must Be Equal Total Liabilities And Owners Equity [ Difference = ' . $request->get('total_liabilities_and_equity_minus_total_assets') .' ]'));
		}
        return redirect()->route('view.non.banking.forecast.income.statement', ['company'=>$company->id,'study'=>$study->id]);
    
    }
	protected function checkSettlementsOrPrincipleValidation(Request $request):array 
	{
		foreach($request->get('otherLongTermAssetsOpeningBalances',[]) as $index=>$otherLongTermAssetsOpeningBalances){
			$amount = $otherLongTermAssetsOpeningBalances['amount']??0;
			$settlements = array_sum($otherLongTermAssetsOpeningBalances['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Settlements Must be Less Than Or Equal Amount In Other Long Term Assets Number ' . ($index+1) . ' [Amount =  ' . number_format($amount) . ' And Settlements = '  . number_format($settlements))
				];
			}
		}
		foreach($request->get('cashAndBankOpeningBalances',[]) as $index=>$cashAndBankOpeningBalances){
			$amount = $cashAndBankOpeningBalances['customer_receivable_amount']??0;
			$settlements = array_sum($cashAndBankOpeningBalances['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Principle Must be Less Than Or Equal Customer Outstanding In Cash And Banks & Customers Outstanding [Customer Outstanding =  ' . number_format($amount) . ' And Total Principle = '  . number_format($settlements))
				];
			}
		}	
		foreach($request->get('supplierPayableOpeningBalances',[]) as $index=>$supplierPayableOpeningBalances){
			$amount = $supplierPayableOpeningBalances['amount']??0;
			$settlements = array_sum($supplierPayableOpeningBalances['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Principle Must be Less Than Or Equal Portfolio Loans Outstanding In ODAs & Portfolio Loans Outstanding [Portfolio Loans Outstanding =  ' . number_format($amount) . ' And Total Principle = '  . number_format($settlements))
				];
			}
		}
		foreach($request->get('otherLongTermAssetsOpeningBalances',[]) as $index=>$otherLongTermAssetsOpeningBalances){
			$amount = $otherLongTermAssetsOpeningBalances['amount']??0;
			$settlements = array_sum($otherLongTermAssetsOpeningBalances['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Settlements Must be Less Than Or Equal Amount In Other Long Term Assets Number ' . ($index+1) . ' [Amount =  ' . number_format($amount) . ' And Total Settlements = '  . number_format($settlements))
				];
			}
		}
		
			foreach($request->get('otherDebtorsOpeningBalances',[]) as $index=>$otherDebtorsOpeningBalances){
			$amount = $otherDebtorsOpeningBalances['amount']??0;
			$settlements = array_sum($otherDebtorsOpeningBalances['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Settlements Must be Less Than Or Equal Amount In Other Debtors Number ' . ($index+1) . ' [Amount =  ' . number_format($amount) . ' And Total Settlements = '  . number_format($settlements))
				];
			}
		}	
		foreach($request->get('otherCreditorsOpeningBalances',[]) as $index=>$otherCreditorsOpeningBalances){
			$amount = $otherCreditorsOpeningBalances['amount']??0;
			$settlements = array_sum($otherCreditorsOpeningBalances['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Settlements Must be Less Than Or Equal Amount In Other Creditors Number ' . ($index+1) . ' [Amount =  ' . number_format($amount) . ' And Total Settlements = '  . number_format($settlements))
				];
			}
		}	foreach($request->get('longTermLoanOpeningBalances',[]) as $index=>$longTermLoanOpeningBalances){
			$amount = $longTermLoanOpeningBalances['amount']??0;
			$settlements = array_sum($longTermLoanOpeningBalances['installments']??[]);

			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Installments Must be Less Than Or Equal Amount In Long Term Loan Number' . ($index+1) . ' [Amount =  ' . number_format($amount) . ' And Total Installments = '  . number_format($settlements))
				];
			}
		}	
		foreach($request->get('longTermLoanOpeningBalances',[]) as $index=>$longTermLoanOpeningBalances){
			$amount = $longTermLoanOpeningBalances['amount']??0;
			$settlements = array_sum($longTermLoanOpeningBalances['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Settlements Must be Less Than Or Equal Amount In Long Term Loan Number ' . ($index+1) . ' [Amount =  ' . number_format($amount) . ' And Total Settlements = '  . number_format($settlements))
				];
			}
		}
		foreach($request->get('otherLongTermLiabilitiesOpeningBalances',[]) as $index=>$otherLongTermLiabilitiesOpeningBalances){
			$amount = $otherLongTermLiabilitiesOpeningBalances['amount']??0;
			$settlements = array_sum($otherLongTermLiabilitiesOpeningBalances['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Settlements Must be Less Than Or Equal Amount In Other Long Term Liabilities Number ' . ($index+1) . ' [Amount =  ' . number_format($amount) . ' And Total Settlements = '  . number_format($settlements))
				];
			}
		}
		
		

		return [
			'status'=>true
		];
	}
    public function getCommonData(Request $request, Company $company)
    {
        return [
            'name'=>$request->get('name'),
            'company_id'=>$company->id ,
        ];
    }
    
    
    
    

}
