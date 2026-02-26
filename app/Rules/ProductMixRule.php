<?php

namespace App\Rules;

use App\Models\NonBankingService\Study;
use Illuminate\Contracts\Validation\ImplicitRule;

class ProductMixRule implements ImplicitRule
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
		$totalProductMixes=[];
		foreach($value as $arr){
			foreach($arr['product_mixes']??[] as $index => $value){
				$totalProductMixes[$index] = isset($totalProductMixes[$index])? $totalProductMixes[$index] + $value : $value; 
			}
		}
		foreach($totalProductMixes as $value){
			if($value == 0 || $value == 100){
				continue ;
			}
			return false ;
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
        return __('Total Product Mix Must Be Zero Or 100%');
    }
}
