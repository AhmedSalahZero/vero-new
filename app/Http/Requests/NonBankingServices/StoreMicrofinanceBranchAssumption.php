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

   
    public function rules()
    {
        return [
            //
        ];
    }
}
