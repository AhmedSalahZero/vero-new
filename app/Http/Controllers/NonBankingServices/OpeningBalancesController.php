<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpeningBalancesRequest;
use App\Models\Company;
use App\Models\NonBankingService\LongTermInvestmentsOpeningBalance;
use App\Models\NonBankingService\Study;
use App\Services\NonBanking\OpeningBalancesStatements;
use App\Services\NonBanking\StudyRecalculation;
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
		// Round 2 (the user's decision): the checks come BEFORE anything is saved. They used to run after the save, so an
		// opening balance sheet that does not balance was saved anyway, with the message on top (studies 99, 102–106 and
		// 137: 104 is out by 113.47 million, the amounts typed in the Name field). The page saves by ajax now, so a refused
		// save keeps what the user typed on the page.
		$checkSettlementsValidations = $this->checkSettlementsOrPrincipleValidation($request);
		if(!$checkSettlementsValidations['status']){
			return $this->refused($request, $checkSettlementsValidations['message']);
		}
		// worked out by StoreOpeningBalancesRequest from the rows sent (not the browser's figure), to the piaster; input()
		// reads it whatever the request's format (get() misses what the request merged into a JSON body)
		$difference = $request->input('total_liabilities_and_equity_minus_total_assets');
		if(round((float) $difference, 2) != 0 ){
			return $this->refused($request, __('Total Assets Must Be Equal Total Liabilities And Owners Equity [ Difference = ' . $difference .' ]'));
		}
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->transaction(function () use ($study, $request, $company) {
			$study->storeRepeaterRelations($request, StudyRecalculation::OPENING_BALANCE_RELATIONS, $company, ['study_id'=>$study->id]);
			// the opening balances' cash in and out of the study months, for the statements (OpeningBalancesStatements)
			OpeningBalancesStatements::refresh($study);
		});
		$redirectTo = route('view.non.banking.forecast.income.statement', ['company'=>$company->id,'study'=>$study->id]);
		if($request->ajax() || $request->expectsJson()){
			return response()->json(['status'=>true, 'message'=>__('Saved'), 'redirectTo'=>$redirectTo]);
		}
        return redirect($redirectTo);
    
    }

	/** a refused save: nothing was saved; by ajax the page keeps what the user typed and shows the message */
	private function refused(Request $request, string $message)
	{
		if($request->ajax() || $request->expectsJson()){
			return response()->json(['message'=>$message, 'errors'=>['opening_balances'=>[$message]]], 422);
		}
		return redirect()->back()->withInput()->with('fail', $message);
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
