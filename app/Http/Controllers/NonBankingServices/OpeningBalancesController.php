<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpeningBalancesRequest;
use App\Models\Company;
use App\Models\NonBankingService\LongTermInvestmentsOpeningBalance;
use App\Models\NonBankingService\Study;
use App\Services\NonBanking\OpeningBalancesStatements;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

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
        ,'supplierPayableOpeningBalances','rightOfUseAssetOpeningBalances','leaseRentLiabilityOpeningBalances','otherCreditorsOpeningBalances','otherLongTermAssetsOpeningBalances','longTermInvestmentsOpeningBalances','otherLongTermLiabilitiesOpeningBalances','equityOpeningBalances','longTermLoanOpeningBalances'
         ], $company, ['study_id'=>$study->id]);
        // the opening balances' cash in and out of the study months, for the statements (OpeningBalancesStatements)
        OpeningBalancesStatements::refresh($study);
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
		foreach($request->get('leaseRentLiabilityOpeningBalances',[]) as $index=>$rightOfUseAssetOpeningBalance){
			$amount = $rightOfUseAssetOpeningBalance['amount']??0;
			$rentPayments = array_sum($rightOfUseAssetOpeningBalance['rent_payment']??[]);
			$rentInterests = array_sum($rightOfUseAssetOpeningBalance['rent_interest']??[]);
			if($rentPayments+$rentInterests>$amount){
				return [
					'status'=>false ,
					'message' => __('Amount Must be Less Than Or Equal [Amount =  ' . number_format($amount) . ' And Total Rent Payment Plus Rent Interests = '  . number_format($rentPayments+$rentInterests))
				];
			}
		}
			
		foreach($request->get('rightOfUseAssetOpeningBalances',[]) as $index=>$rightOfUseAssetOpeningBalance){
			$amount = $rightOfUseAssetOpeningBalance['amount']??0;
			$settlements = array_sum($rightOfUseAssetOpeningBalance['payload']??[]);
			if($settlements>$amount){
				return [
					'status'=>false ,
					'message' => __('Amount Must be Less Than Or Equal [Amount =  ' . number_format($amount) . ' And Total Amortization = '  . number_format($settlements))
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
