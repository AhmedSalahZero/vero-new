<?php

namespace App\Rules;

use App\Models\SupplierInvoice;
use Illuminate\Contracts\Validation\ImplicitRule;

class ValidAllocationsRule implements ImplicitRule
{
   
	protected $failedMessage = null;
    public function __construct()
    {
        //
    }

    public function passes($attribute, $allocationItems)
    {

        foreach((array)$allocationItems as $invoiceId=>$arrayOfAllocations){
			if(!is_array($arrayOfAllocations)){
				continue;
			}
			/**
			 * * الأرقام جاية من الفورم كنصوص متفرمتة (1,000.00) ، فلازم تتفك الأول
			 * * قبل الجمع والمقارنة ، غير كده array_sum بيرمي وارنينج على النصوص
			 * * وبيجمع القيم غلط (1,000.00 كانت بتتحسب 1)
			 */
			$totalAllocationsForInvoiceNumber = array_sum(array_map(
				fn($allocationAmount) => (float) number_unformat((string) $allocationAmount),
				array_column($arrayOfAllocations,'allocation_amount')
			));
			$settlementAmount = (float) number_unformat((string) Request()->input('settlements.'.$invoiceId.'.settlement_amount',0));
			if($totalAllocationsForInvoiceNumber > $settlementAmount){
				$supplierInvoice = SupplierInvoice::find($invoiceId);
				$invoiceNumber = $supplierInvoice ? $supplierInvoice->getInvoiceNumber() : $invoiceId;
				$this->failedMessage  = __('Invalid Allocation For Invoice :invoiceNumber' ,['invoiceNumber'=>$invoiceNumber]);
				return false ;
			}

		}
		return true ;

    }

   
    public function message()
    {
        return $this->failedMessage;
    }
}
