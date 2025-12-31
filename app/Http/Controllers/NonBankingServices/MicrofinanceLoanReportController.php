<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\ExistingBranch;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class MicrofinanceLoanReportController extends MicrofinanceLoanController
{
    use NonBankingService ;
    public function create(Company $company, Request $request, Study $study , $branchId = null )
    {
        return view('non_banking_services.microfinance.loan-report', $this->getViewVars($company, $study,$branchId));
    }
   
 

  

}
