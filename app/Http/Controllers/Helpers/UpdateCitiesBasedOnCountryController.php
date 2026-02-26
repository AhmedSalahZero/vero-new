<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use App\Models\Repositories\CountryRepository;
use Illuminate\Http\Request;

class UpdateCitiesBasedOnCountryController extends Controller
{
	private CountryRepository $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository ;
    }
    
    public function __invoke(Request $request)
    {
$result = '<option>'.  __('Select') .'</option> ';


        $country = $this->countryRepository->find($request->country_id);

        if($country)
        {
            $states = $country->states()->get();
            $result =  formatSelects($states , $request->selectedItem , $request->model_id , $request->model_value);

        }
        return response()->json([
            'status'=>true ,
            'append_id'=>$request->append_id ,
            'result'=>$result
        ]);
        
    }
}
