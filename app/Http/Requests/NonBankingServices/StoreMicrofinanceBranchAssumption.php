<?php

namespace App\Http\Requests\NonBankingServices;

use App\Models\NonBankingService\Study;
use Illuminate\Foundation\Http\FormRequest;

class StoreMicrofinanceBranchAssumption extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
	public function prepareForValidation()
	{
		// $study = Study::find($this->study_id);
		// $dateWithDateIndex = $study->getDateWithDateIndex();
		// $newBranches = $this->get('newBranchMicrofinanceOpeningProjections',[]) ;
		// foreach($newBranches as $index => &$itemArr){
		// 	$startDateAsString = $itemArr['start_date_as_string'];
		// 	$itemArr['start_date_as_index'] = $dateWithDateIndex[$startDateAsString];
		// }
		// $this->merge([
		// 	'newBranchMicrofinanceOpeningProjections'=>$newBranches 
		// ]);
	}
    public function rules()
    {
        return [
            //
        ];
    }
}
