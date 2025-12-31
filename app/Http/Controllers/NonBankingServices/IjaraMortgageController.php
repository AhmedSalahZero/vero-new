<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreIjaraMortgageRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\IjaraMortgageRevenueStreamBreakdown;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class IjaraMortgageController extends Controller
{
	use NonBankingService ;
	public function getModel():IjaraMortgageRevenueStreamBreakdown
	{
		return new IjaraMortgageRevenueStreamBreakdown();
	}
	public function create(Company $company , Request $request,Study $study){
		$model = $this->getModel();
		return view($model->getFormName(), $this->getModel()->getViewVars($company,$study));
	}
	public function getRepeaterRelations():array 
	{
		return [
			'ijaraMortgageBreakdowns'
		];
	}
	public function store(Company $company , StoreIjaraMortgageRevenueStreamRequest $request,Study $study)
	{
		$study->storeRelationsWithNoRepeater($request,$company,['seasonality']);
		$study->storeRepeaterRelations($request,$this->getRepeaterRelations(),$company);
		$study->syncSeasonality($request->get('seasonality',[]),Study::IJARA , $company->id );
		$study->storeFixedLoans($request,Study::IJARA,'ijaraMortgageBreakdowns');
		$study->updateExpensesPercentageAndCostPerUnitsOfSales();
		return response()->json([
			'redirectTo'=>$study->getRevenueRoute(Study::PORTFOLIO_MORTGAGE)
		]);
	}
}
