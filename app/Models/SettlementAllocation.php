<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperSettlementAllocation
 */
class SettlementAllocation extends Model
{
	
	protected $guarded = ['id'];
	

	public function moneyPayment()
	{
		return $this->belongsTo(MoneyPayment::class,'money_payment_id','id');
	}
	public function letterOfCreditIssuance():BelongsTo
	{
		return $this->belongsTo(LetterOfCreditIssuance::class,'letter_of_credit_issuance_id','id');
	}
	public function contract():BelongsTo
	{
		return $this->belongsTo(Contract::class,'contract_id','id');
	}
	public function supplierInvoice():BelongsTo
	{
		return $this->belongsTo(SupplierInvoice::class,'invoice_id','id');
	}
	public function getInvoiceNumber()
	{
		return $this->supplierInvoice ? $this->supplierInvoice->getInvoiceNumber() : __('N/A') ;
	}
	public function getAmount()
	{
		return $this->allocation_amount ;
	}

	
	
	public static function getSettlementAllocationPerContractAndLetterOfCreditIssuance(array &$result ,$foreignExchangeRates , $mainFunctionalCurrency ,string $dateFieldName,int $contractId , int $customerId, string $startDate , string $endDate , string $currentWeekYear , int $companyId  ):void
	{
		
		$keyNameForCurrentType = __('Letter Of Credit');
		
		$settlementAllocations  =  self::where('settlement_allocations.contract_id',$contractId)->with(['letterOfCreditIssuance','letterOfCreditIssuance.supplier'])
			->join('letter_of_credit_issuances','settlement_allocations.letter_of_credit_issuance_id','=','letter_of_credit_issuances.id')
			->where('settlement_allocations.partner_id',$customerId)
			->whereBetween($dateFieldName,[$startDate,$endDate])
			->where('letter_of_credit_issuances.company_id',$companyId)
			->get(['settlement_allocations.contract_id','invoice_id','settlement_allocations.letter_of_credit_issuance_id','allocation_amount','payment_currency','payment_date']);
			foreach($settlementAllocations as $settlementAllocation){
				$supplier = $settlementAllocation->letterOfCreditIssuance->supplier ;
				$paymentCurrency = $settlementAllocation->payment_currency ;
				$paymentDate = $settlementAllocation->payment_date ;
				$exchangeRate =  $paymentCurrency!= $mainFunctionalCurrency ? ForeignExchangeRate::getExchangeRateAt($paymentCurrency,$mainFunctionalCurrency,$paymentDate,$companyId,$foreignExchangeRates) : 1 ;
				$invoiceId = $settlementAllocation->invoice_id ; 
				$invoiceNumber = SupplierInvoice::find($invoiceId)->getInvoiceId() ; 
				$keyNameForCurrentType = $keyNameForCurrentType.' - '. __('Invoice No') .' ' .$invoiceNumber ;
				$currentAmountAllocationAmount = $settlementAllocation->allocation_amount * $exchangeRate ;
				$supplierName = $supplier->getName();
				$result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear] = isset($result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear]) ? $result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear] + $currentAmountAllocationAmount :  $currentAmountAllocationAmount;
				$result['suppliers'][$supplierName][$keyNameForCurrentType]['total'] = isset($result['suppliers'][$supplierName][$keyNameForCurrentType]['total']) ? $result['suppliers'][$supplierName][$keyNameForCurrentType]['total']  + $currentAmountAllocationAmount : $currentAmountAllocationAmount;
				$currentTotal = $currentAmountAllocationAmount;
				$result['suppliers'][$supplierName]['total'][$currentWeekYear] = isset($result['suppliers'][$supplierName]['total'][$currentWeekYear]) ? $result['suppliers'][$supplierName]['total'][$currentWeekYear] +  $currentTotal : $currentTotal ;
			}
	
	}
	
	
}	
