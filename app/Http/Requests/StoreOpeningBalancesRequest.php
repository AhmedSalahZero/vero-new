<?php

namespace App\Http\Requests;

use App\Models\NonBankingService\Study;
use Illuminate\Foundation\Http\FormRequest;

class StoreOpeningBalancesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
		return [];

    }
	
	protected function prepareForValidation()
    {
        $project = Request()->route('project');
        $fixedAssets = Request()->get('fixedAssetOpeningBalances', []);
        foreach ($fixedAssets as $index => &$fixedAssetOpeningArr) {
            $fixedAssetOpeningArr['gross_amount'] = number_unformat($fixedAssetOpeningArr['gross_amount']);
            $fixedAssetOpeningArr['accumulated_depreciation'] = number_unformat($fixedAssetOpeningArr['accumulated_depreciation']);
            unset($fixedAssetOpeningArr['product_id']);
            unset($fixedAssetOpeningArr['percentage']);
			if(!isset($fixedAssetOpeningArr['name_id']) || is_null($fixedAssetOpeningArr['name_id'])){
				unset($fixedAssets[$index]);
			}
        }
		
		
		$data  = Request()->all() ;
		$netFixedAsset = 0 ;
		$fixedAssetsArrs  = $data['fixedAssetOpeningBalances']??[];
		foreach($fixedAssetsArrs as $fixedAssetsArr){
			$currentNetFixedAsset = ($fixedAssetsArr['gross_amount']??0)  - ($fixedAssetsArr['accumulated_depreciation']??0);
			$netFixedAsset+= $currentNetFixedAsset ; 
		}
		$odasOutstandingOpeningAmount  = array_sum(array_column($data['supplierPayableOpeningBalances']??[],'odas_outstanding_opening_amount'));
		
		$totalCashAndBanks  = array_sum(array_column($data['cashAndBankOpeningBalances']??[],'cash_and_bank_amount'));
		$totalCustomerReceivableAmount  = array_sum(array_column($data['cashAndBankOpeningBalances']??[],'customer_receivable_amount'));
		$ecl  = array_sum(array_column($data['cashAndBankOpeningBalances']??[],'expected_credit_loss'));
		
		$totalOtherDebtorsAmount  = array_sum(array_column($data['otherDebtorsOpeningBalances']??[],'amount'));
		$totalSupplierPayableAmount  = array_sum(array_column($data['supplierPayableOpeningBalances']??[],'amount'));
		$totalCreditorPayableAmount  = array_sum(array_column($data['otherCreditorsOpeningBalances']??[],'amount'));
		$totalVatAmount  = array_sum(array_column($data['vatAndCreditWithholdTaxesOpeningBalances']??[],'vat_amount'));
		$totalWithholdAmount  = array_sum(array_column($data['vatAndCreditWithholdTaxesOpeningBalances']??[],'credit_withhold_taxes'));
		$totalLoanAmount  = array_sum(array_column($data['longTermLoanOpeningBalances']??[],'amount'));
		$totalOtherLongAmount  = array_sum(array_column($data['otherLongTermLiabilitiesOpeningBalances']??[],'amount'));
		$totalOtherLongAssetAmount  = array_sum(array_column($data['otherLongTermAssetsOpeningBalances']??[],'amount'));
		$totalPaidUpAmount  = array_sum(array_column($data['equityOpeningBalances']??[],'paid_up_capital_amount'));
		$totalLegalReserveAmount  = array_sum(array_column($data['equityOpeningBalances']??[],'legal_reserve'));
		$totalRetainedEarningsAmount  = array_sum(array_column($data['equityOpeningBalances']??[],'retained_earnings'));
		$totalAssets =  $netFixedAsset + $totalCashAndBanks + $totalCustomerReceivableAmount+$totalOtherDebtorsAmount+$totalOtherLongAssetAmount+$ecl;
		$totalLiabilitiesAndEquity = $totalSupplierPayableAmount+$totalCreditorPayableAmount+$totalVatAmount+$totalWithholdAmount+$totalLoanAmount+$totalOtherLongAmount+$totalPaidUpAmount+$totalLegalReserveAmount+$totalRetainedEarningsAmount+$odasOutstandingOpeningAmount;
		// dd($totalLiabilitiesAndEquity ,$totalAssets );
        $this->merge([
            'fixedAssetOpeningBalances'=>$fixedAssets,
			'total_liabilities_and_equity_minus_total_assets'=>$totalLiabilitiesAndEquity-$totalAssets
        ]);
    
    }
	
}

