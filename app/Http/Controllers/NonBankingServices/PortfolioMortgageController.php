<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HArr;
use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StorePortfolioMortgageRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory;
use App\Models\NonBankingService\PortfolioMortgageRevenueStreamBreakdown;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortfolioMortgageController extends Controller
{
    use NonBankingService ;
    public function getModel():PortfolioMortgageRevenueStreamBreakdown
    {
        return new PortfolioMortgageRevenueStreamBreakdown();
    }
    public function create(Company $company, Request $request, Study $study)
    {
        $model = $this->getModel();
        return view($model->getFormName(), $this->getModel()->getViewVars($company, $study));
    }

    protected function getRepeaterRelations():array
    {
        return [
            'portfolioMortgageRevenueProjectionByCategories'
        ];
    }
    public function store(Company $company, StorePortfolioMortgageRevenueStreamRequest $request, Study $study)
    {
            
        $study->storeRelationsWithNoRepeater($request, $company);
        $study->storeRepeaterRelations($request, $this->getRepeaterRelations(), $company);
    
    
        // question here
		
		$study->recalculatePortfolioMortgage($request);
		
		$study->updateExpensesPercentageAndCostPerUnitsOfSales();
        if($request->get('save') === 'calculate-portfolio'){
			return response()->json([
            'redirectTo'=>route('create.portfolio.mortgage.revenue.stream.breakdown',['company'=>$company->id,'study'=>$study->id])
        ]); 
		}
        return response()->json([
            'redirectTo'=>$study->getRevenueRoute(Study::MICROFINANCE)
        ]);
    }
    public function addNewCategory(Request $request, Company $company, Study $study)
    {
        $study->portfolioMortgageRevenueProjectionByCategories()->create([
            'company_id'=>$company->id
        ]);
        return redirect()->back();
    }
    public function deleteCategory(Request $request, Company $company, Study $study, PortfolioMortgageRevenueProjectionByCategory $portfolioMortgageCategory)
    {
        $portfolioMortgageCategoryId= $portfolioMortgageCategory->id ;
        $studyId = $study->id;
        DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->where('study_id', $studyId)->where('revenue_stream_type', Study::PORTFOLIO_MORTGAGE)->where('revenue_stream_id', $portfolioMortgageCategoryId)->delete();
        DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('sensitivity_loan_schedule_payments')->where('study_id', $studyId)->where('revenue_stream_type', Study::PORTFOLIO_MORTGAGE)->where('revenue_stream_id',$portfolioMortgageCategoryId)->delete();
        $portfolioMortgageCategory->delete();
        return redirect()->back();
    }
}
