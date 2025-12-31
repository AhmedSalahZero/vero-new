<?php

namespace App\Http\Controllers\NonBankingServices;


use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreReverseFactoringRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;


class ReverseFactoringController extends Controller
{
	use NonBankingService ;
	public function getModel():ReverseFactoringRevenueStreamBreakdown
	{
		return new ReverseFactoringRevenueStreamBreakdown();
	}
	public function create(Company $company , Request $request,Study $study){
		$model = $this->getModel();
		return view($model->getFormName(), $this->getModel()->getViewVars($company,$study));
	}
	public function getRepeaterRelations():array 
	{
		return [
			'reverseFactoringBreakdowns'
		];
	}
	public function store(Company $company , StoreReverseFactoringRevenueStreamRequest $request,Study $study)
	{
			$study->storeRelationsWithNoRepeater($request,$company,['seasonality']);
			$study->storeRepeaterRelations($request,$this->getRepeaterRelations(),$company);
			$study->syncSeasonality($request->get('seasonality',[]),Study::REVERSE_FACTORING , $company->id ) ;
			$study->storeVariableLoans($request,Study::REVERSE_FACTORING,'reverseFactoringBreakdowns');
			$study->updateExpensesPercentageAndCostPerUnitsOfSales();
		return response()->json([
			'redirectTo'=>$study->getRevenueRoute(Study::IJARA)
		]);
	}
}
