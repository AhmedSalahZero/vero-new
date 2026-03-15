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

    public function rules()
    {
        return [];

    }

    /**
     * Recursively apply number_unformat to all numeric-like scalars in the request data.
     */
    protected function prepareForStore()
    {
        $data = $this->all();

        $data = $this->numberUnformatDeep($data);

        // Replace the underlying input bag with the normalized data
        $this->replace($data);
    }

    /**
     * Walk the given value and apply number_unformat on numeric-like scalars.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function numberUnformatDeep($value)
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                // Never touch CSRF / framework meta fields
                if ($key === '_token' || $key === '_method') {
                    $normalized[$key] = $item;
                    continue;
                }

                $normalized[$key] = $this->numberUnformatDeep($item);
            }

            return $normalized;
        }

        // Only attempt to unformat scalar numeric-like strings
        if (is_string($value)) {
            // If the string contains only number formatting characters, unformat it
            if ($value !== '' && preg_match('/^[0-9\.\,\-\s]+$/', $value)) {
                return number_unformat($value);
            }

            return $value;
        }

        // For ints/floats we can leave as-is; for other types just return original
        return $value;
    }
        
    protected function prepareForValidation():array 
    {
        $this->prepareForStore();
        $fixedAssets = Request()->get('fixedAssetOpeningBalances', []);
        foreach ($fixedAssets as $index => &$fixedAssetOpeningArr) {
            $fixedAssetOpeningArr['gross_amount'] = number_unformat($fixedAssetOpeningArr['gross_amount']);
            $fixedAssetOpeningArr['accumulated_depreciation'] = number_unformat($fixedAssetOpeningArr['accumulated_depreciation']);
            unset($fixedAssetOpeningArr['product_id']);
            unset($fixedAssetOpeningArr['percentage']);
            if (!isset($fixedAssetOpeningArr['name_id']) ) {
                unset($fixedAssets[$index]);
            }
        }
            
        $data  = $this->all() ;
        $netFixedAsset = 0 ;
        $fixedAssetsArrs  = $data['fixedAssetOpeningBalances']??[];
        foreach ($fixedAssetsArrs as $fixedAssetsArr) {
            $currentNetFixedAsset = ($fixedAssetsArr['gross_amount']??0)  - ($fixedAssetsArr['accumulated_depreciation']??0);
            $netFixedAsset+= $currentNetFixedAsset ;
        }
        $odasOutstandingOpeningAmount  = array_sum(array_column($data['supplierPayableOpeningBalances']??[], 'odas_outstanding_opening_amount'));
        $totalCashAndBanks  = array_sum(array_column($data['cashAndBankOpeningBalances']??[], 'cash_and_bank_amount'));
        $totalCustomerReceivableAmount  = array_sum(array_column($data['cashAndBankOpeningBalances']??[], 'customer_receivable_amount'));
        $ecl  = array_sum(array_column($data['cashAndBankOpeningBalances']??[], 'expected_credit_loss'));
            
        $totalOtherDebtorsAmount  = array_sum(array_column($data['otherDebtorsOpeningBalances']??[], 'amount'));
        $totalSupplierPayableAmount  = array_sum(array_column($data['supplierPayableOpeningBalances']??[], 'amount'));
        $totalCreditorPayableAmount  = array_sum(array_column($data['otherCreditorsOpeningBalances']??[], 'amount'));
        $totalVatAmount  = array_sum(array_column($data['vatAndCreditWithholdTaxesOpeningBalances']??[], 'vat_amount'));
        $totalWithholdAmount  = array_sum(array_column($data['vatAndCreditWithholdTaxesOpeningBalances']??[], 'credit_withhold_taxes'));
        $totalLoanAmount  = array_sum(array_column($data['longTermLoanOpeningBalances']??[], 'amount'));
        $totalOtherLongAmount  = array_sum(array_column($data['otherLongTermLiabilitiesOpeningBalances']??[], 'amount'));
        $totalOtherLongAssetAmount  = array_sum(array_column($data['otherLongTermAssetsOpeningBalances']??[], 'amount'));
        $totalLongTermInvestmentAmount  = array_sum(array_column($data['longTermInvestmentsOpeningBalances']??[], 'amount'));
        $totalPaidUpAmount  = array_sum(array_column($data['equityOpeningBalances']??[], 'paid_up_capital_amount'));
        $totalLegalReserveAmount  = array_sum(array_column($data['equityOpeningBalances']??[], 'legal_reserve'));
        $totalRetainedEarningsAmount  = array_sum(array_column($data['equityOpeningBalances']??[], 'retained_earnings'));
        $rightOfUseAssetAmount  = array_sum(array_column($data['rightOfUseAssetOpeningBalances']??[], 'amount'));
        $totalLeaseRentLiabilityAmount  = array_sum(array_column($data['leaseRentLiabilityOpeningBalances']??[], 'amount'));
        $totalAssets =  $netFixedAsset + $totalCashAndBanks + $totalCustomerReceivableAmount+$rightOfUseAssetAmount+$totalOtherDebtorsAmount+$totalOtherLongAssetAmount+$totalLongTermInvestmentAmount+$ecl;
        $totalLiabilitiesAndEquity = $totalSupplierPayableAmount+$totalCreditorPayableAmount+$totalLeaseRentLiabilityAmount+$totalVatAmount+$totalWithholdAmount+$totalLoanAmount+$totalOtherLongAmount+$totalPaidUpAmount+$totalLegalReserveAmount+$totalRetainedEarningsAmount+$odasOutstandingOpeningAmount;
            
        $this->merge([
            'fixedAssetOpeningBalances'=>$fixedAssets,
            'total_liabilities_and_equity_minus_total_assets'=>$totalLiabilitiesAndEquity-$totalAssets
        ]);
        return [];
        
    }
        
}
