<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class MicrofinanceProductMixControllerController extends Controller
{
    use NonBankingService ;
    public function create(Company $company, Request $request, Study $study)
    {
        return view('non_banking_services.microfinance-product-mix.form', $this->getViewVars($company, $study));
    }
    protected function getViewVars(Company $company, Study $study)
    {
		
        $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
        $isYearsStudy = !$study->isMonthlyStudy();
        return [
			'microfinanceProductMixCount'=>$study->microfinance_product_mix_count,
			'company'=>$company ,
			'products'=>$company->getActiveMicrofinanceProducts(),
            'model'=>$study ,
			'study'=>$study,
            'title'=>__('Microfinance Products Mix'),
            'storeRoute'=>route('store.microfinance.product.mix', ['company'=>$company->id , 'study'=>$study->id]),
            'yearsWithItsMonths' =>$yearsWithItsMonths,
            'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
            'isYearsStudy'=>$isYearsStudy
        ];
    }

    public function store(Company $company, Request $request, Study $study )
    {
	
		$study->update([
			'product_mix_senior_loan_officers'=>$request->get('product_mix_senior_loan_officers',[]),
			'product_mix_loan_officers'=>$request->get('product_mix_loan_officers',[]),
		]);
		$study->storeRepeaterRelations($request,['microfinanceByBranchProductMixes'],$company,[]);
		return response()->json([
                'redirectTo'=>route('create.by-branch.microfinance', ['company'=>$company->id,'study'=>$study->id])
            ]);
        
      
    }
	
	
}
