<?php

namespace App\Rules;

use App\Helpers\HArr;
use App\Models\NonBankingService\Study;
use Illuminate\Contracts\Validation\ImplicitRule;

class AtLeastOneRevenueMustBeSelectedRule implements ImplicitRule
{
	protected string $errorMessage ;
    
    public function __construct()
    {
        
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
       $revenueTypes = Study::getRevenueStreamTypes();
	   $revenueTypesExceptSecuritization = HArr::removeKeysFromArray($revenueTypes,['has_securitization']);
	   if(Request()->has('has_securitization') ){
		foreach(['has_leasing','has_ijara_mortgage','has_micro_finance'] as $currentRevenueType){
			if(Request()->has($currentRevenueType)){
				return true ;
			}
		}
		$this->errorMessage = __('Please Select At Least One Securitized Revenue With Securitization');
		return false ;
	   }
	   foreach($revenueTypesExceptSecuritization as $revenueType => $revenueTitle){
			if(Request()->has($revenueType)){
				return true ;
			}
	   }
	   $this->errorMessage = __('Please Select At Least One Revenue Type');
	   return false ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
