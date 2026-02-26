<?php

namespace App\Http\Controllers\NonBankingServices;


use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Position;
use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;


class AjaxController extends Controller
{
	public function getStreamCategoryBasedOnRevenueStream(Request $request,Company $company,Study $study)
	{
		$revenueStreamIds  = $request->get('revenueStreamId',[]);
		$result = $study->getSelectedRevenueStreamWithCategories($revenueStreamIds);
		return response()->json([
			'status'=>true ,
			'data'=>$result
		]);

	}
	public function getPositionsBasedOnDepartments(Request $request)
	{
		$departmentIds = $request->get('departmentIds',[]);
		$positionIds = Position::whereIn('department_id',$departmentIds)->pluck('name','id')->toArray();
		return response()->json([
			'positionIds'=>$positionIds			
		]);
	}
	
}
