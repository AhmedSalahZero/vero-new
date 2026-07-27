<?php

namespace App\Rules;

use App\Models\NonBankingService\Study;
use Illuminate\Contracts\Validation\ImplicitRule;

class StartDateAndOperationDateRule implements ImplicitRule
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
		try{
			$studyOperationDate=  $this->study->operation_start_date;
		$studyOperationDateAsIndex = $this->study->convertDateStringToDateIndex($studyOperationDate);
		foreach($value as $arr){
			$startDate = $arr['start_date'].'-01';
			$startDateAsIndex = $this->study->convertDateStringToDateIndex($startDate);
			$operationDate =$arr['operation_date'].'-01';
			$operationDateAsIndex = $this->study->convertDateStringToDateIndex($operationDate);
			$firstCondition = $startDateAsIndex < $studyOperationDateAsIndex;
			$secondCondition = $operationDateAsIndex < $startDateAsIndex;
			if($firstCondition || $secondCondition ){
				if($firstCondition){
					$this->failedMessage = __('Branch Start Date Must Be Less Than Or Equal Study Operation Date');
				}else{
					$this->failedMessage = __('Branch Operation Date Must Be Greater Than Or Equal Branch Start Date');
				}
				return false ;
			}
		}
	}
	catch(\Exception $e){
			$this->failedMessage = __('Branch Start Date Must Be Less Than Or Equal Study Operation Date');
			return false;
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
        return $this->failedMessage;
    }
}
