<?php

namespace App\Http\Requests;


class UpdateFullySecuredOverdraftRequest extends StoreFullySecuredOverdraftRequest
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
		$excludeAccountNumbers = (array)Request()->route('fullySecuredOverdraft')->getAccountNumber();
        return array_merge(
			parent::rules($excludeAccountNumbers),
			[]
		);
    }
}
