<?php

namespace App\Rules;

use App\Models\NonBankingService\Study;
use Illuminate\Contracts\Validation\ImplicitRule;

class ProductSeasonalityRule implements ImplicitRule
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
		if($this->study->isMonthlyStudy()){
			return true;
		}
		foreach((array)$value as $arr){
			$totalSeasonality = 0 ;
			foreach($arr['seasonality']??[] as $index => $value){
				$totalSeasonality+= $value;
			}
			$condition = $totalSeasonality >= 99.8 && $totalSeasonality <= 100 ;
			if(!$condition){
				return false ;
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
        return __('Seasonality Must Be 100%');
    }
}
