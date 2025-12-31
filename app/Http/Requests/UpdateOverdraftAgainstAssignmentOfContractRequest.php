<?php

namespace App\Http\Requests;

use App\Models\OverdraftAgainstAssignmentOfContract;


class UpdateOverdraftAgainstAssignmentOfContractRequest extends StoreOverdraftAgainstAssignmentOfContractRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(array $excludeAccountNumbers = [])
    {
		$odAgainstAssignmentOfContract = Request()->route('odAgainstAssignmentOfContract') ;
		/**
		 * @var OverdraftAgainstAssignmentOfContract  $odAgainstAssignmentOfContract ;
		 */
		$excludeAccountNumbers = (array)$odAgainstAssignmentOfContract->getAccountNumber();
        return array_merge(
			parent::rules($excludeAccountNumbers),
			[]
		);
    }
}
