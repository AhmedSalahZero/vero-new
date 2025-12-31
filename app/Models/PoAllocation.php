<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PoAllocation extends Model
{
	
	protected $guarded = ['id'];
	

	public function moneyPayment()
	{
		return $this->belongsTo(MoneyPayment::class,'money_payment_id','id');
	}
	public function letterOfCreditIssuance()
	{
		return $this->belongsTo(LetterOfCreditIssuance::class,'letter_of_credit_issuance_id','id');
	}
	public function contract()
	{
		return $this->belongsTo(Contract::class,'contract_id','id');
	}
	public function supplierInvoice()
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
	public function getPercentage()
	{
		return $this->allocation_percentage ;
	}
	public static function getSettlementAllocationPerContractAndMoneyType(array &$result   , string $moneyType,string $dateFieldName,int $contractId , int $customerId, string $startDate , string $endDate , string $currentWeekYear,string $currencyName , int $companyId , ?string $chequeStatus = null   ):void
	{
		return ;
		$keyNameForCurrentType = [
			MoneyPayment::OUTGOING_TRANSFER => __('Outgoing Transfers'),
			MoneyPayment::CASH_PAYMENT =>__('Cash Payments'),
			MoneyPayment::PAYABLE_CHEQUE => $chequeStatus == PayableCheque::PAID ? __('Paid Payable Cheques') : __('Under Payment Payable Cheques')
		][$moneyType];
		
		$settlementAllocations  =  self::where('settlement_allocations.contract_id',$contractId)->with(['moneyPayment','moneyPayment.supplier'])
			->join('money_payments','settlement_allocations.money_payment_id','=','money_payments.id')
			->where('money_payments.type',$moneyType)
			->where('money_payments.company_id',$companyId)
			->where('settlement_allocations.partner_id',$customerId)
			->where('currency',$currencyName)
			->whereBetween($dateFieldName,[$startDate,$endDate])
			->when($chequeStatus , function(Builder $builder) use ($chequeStatus){
				$builder->join('payable_cheques','payable_cheques.money_payment_id','=','money_payments.id')
				->where('payable_cheques.status',$chequeStatus);
			})
			->get(['settlement_allocations.contract_id','invoice_id','settlement_allocations.money_payment_id','allocation_amount']);
			
			foreach($settlementAllocations as $settlementAllocation){
				$supplier = $settlementAllocation->moneyPayment->supplier ;
				$invoiceId = $settlementAllocation->invoice_id ; 
				$invoiceNumber=SupplierInvoice::find($invoiceId)->getInvoiceNumber();
				$keyNameForCurrentType = $keyNameForCurrentType.' - '. __('Invoice No') .' ' .$invoiceNumber ;
				$currentAmountAllocationAmount = $settlementAllocation->allocation_amount ;
				if($currentAmountAllocationAmount <= 0){
					continue;
				}
				// $supplierName = $supplier->getName();
				$result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear] = isset($result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear]) ? $result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear] + $currentAmountAllocationAmount :  $currentAmountAllocationAmount;
				$result['suppliers'][$supplierName][$keyNameForCurrentType]['total'] = isset($result['suppliers'][$supplierName][$keyNameForCurrentType]['total']) ? $result['suppliers'][$supplierName][$keyNameForCurrentType]['total']  + $currentAmountAllocationAmount : $currentAmountAllocationAmount;
				$currentTotal = $currentAmountAllocationAmount;
				$result['suppliers'][$supplierName]['total'][$currentWeekYear] = isset($result['suppliers'][$supplierName]['total'][$currentWeekYear]) ? $result['suppliers'][$supplierName]['total'][$currentWeekYear] +  $currentTotal : $currentTotal ;
				// $result['suppliers'][$supplierName]['total']['total_of_total'] = isset($result['suppliers'][$supplierName]['total']['total_of_total']) ? $result['suppliers'][$supplierName]['total']['total_of_total'] + $result['suppliers'][$supplierName]['total'][$currentWeekYear] : $result['suppliers'][$supplierName]['total'][$currentWeekYear];
		//		$totalCashOutFlowArray[$currentWeekYear] = isset($totalCashOutFlowArray[$currentWeekYear]) ? $totalCashOutFlowArray[$currentWeekYear] +   $currentTotal : $currentTotal ;
			}
	}
	
	
	public static function getSettlementAllocationPerContractAndLetterOfCreditIssuance(array &$result  ,string $dateFieldName,int $contractId , int $customerId, string $startDate , string $endDate , string $currentWeekYear , int $companyId  ):void
	{
		
		$keyNameForCurrentType = __('Letter Of Credit');
		
		$settlementAllocations  =  self::where('settlement_allocations.contract_id',$contractId)->with(['letterOfCreditIssuance','letterOfCreditIssuance.supplier'])
			->join('letter_of_credit_issuances','settlement_allocations.letter_of_credit_issuance_id','=','letter_of_credit_issuances.id')
			->where('settlement_allocations.partner_id',$customerId)
			->whereBetween($dateFieldName,[$startDate,$endDate])
			->where('letter_of_credit_issuances.company_id',$companyId)
			->get(['settlement_allocations.contract_id','invoice_id','settlement_allocations.letter_of_credit_issuance_id','allocation_amount']);
			foreach($settlementAllocations as $settlementAllocation){
				$supplier = $settlementAllocation->letterOfCreditIssuance->supplier ;
				$invoiceId = $settlementAllocation->invoice_id ; 
				$invoiceNumber = SupplierInvoice::find($invoiceId)->getInvoiceId() ; 
				$keyNameForCurrentType = $keyNameForCurrentType.' - '. __('Invoice No') .' ' .$invoiceNumber ;
				$currentAmountAllocationAmount = $settlementAllocation->allocation_amount ;
				$supplierName = $supplier->getName();
				$result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear] = isset($result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear]) ? $result['suppliers'][$supplierName][$keyNameForCurrentType]['weeks'][$currentWeekYear] + $currentAmountAllocationAmount :  $currentAmountAllocationAmount;
				$result['suppliers'][$supplierName][$keyNameForCurrentType]['total'] = isset($result['suppliers'][$supplierName][$keyNameForCurrentType]['total']) ? $result['suppliers'][$supplierName][$keyNameForCurrentType]['total']  + $currentAmountAllocationAmount : $currentAmountAllocationAmount;
				$currentTotal = $currentAmountAllocationAmount;
				$result['suppliers'][$supplierName]['total'][$currentWeekYear] = isset($result['suppliers'][$supplierName]['total'][$currentWeekYear]) ? $result['suppliers'][$supplierName]['total'][$currentWeekYear] +  $currentTotal : $currentTotal ;
				// $result['suppliers'][$supplierName]['total']['total_of_total'] = isset($result['suppliers'][$supplierName]['total']['total_of_total']) ? $result['suppliers'][$supplierName]['total']['total_of_total'] + $result['suppliers'][$supplierName]['total'][$currentWeekYear] : $result['suppliers'][$supplierName]['total'][$currentWeekYear];
	//			$totalCashOutFlowArray[$currentWeekYear] = isset($totalCashOutFlowArray[$currentWeekYear]) ? $totalCashOutFlowArray[$currentWeekYear] +   $currentTotal : $currentTotal ;
			}
	
	}
	
	
}	
