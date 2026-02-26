<?php

namespace App\Rules;

use App\Models\NonBankingService\Study;
use Illuminate\Contracts\Validation\ImplicitRule;

class FlatRateRule implements ImplicitRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
	protected Study $study;
	protected string $failedMessage;
    public function __construct(Study $study )
    {
        $this->study = $study;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
		foreach($value as $productId => $salesProjectionArr){
			$avgAmount = $salesProjectionArr['avg_amount']??0;
			if($avgAmount>0){
				foreach($salesProjectionArr['flat_rates'] as $dateAsIndex => $flatRate){
					$feesRate = $salesProjectionArr['fees_rates'][$dateAsIndex]??0;
					if($feesRate > $flatRate){
						return false ;
					}
				}
			}
			
		}
		return true ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Flat Rate Must Be Equal Or Greater Than Setup Fees Rate');
    }
}
