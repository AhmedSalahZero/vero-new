<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterColumnBasedOnAnotherColumnController extends Controller
{
    public function filter(Request $request,Company $company){
		$tableName = $request->get('filterTableName');
		$firstColumnName = $request->get('mainColumnName');
		$firstColumnValues = $request->get('mainColumnValues',[]);
		$secondColumnName = $request->get('secondColumnName');
		$secondColumnValues = $request->get('secondColumnValues',[]);
		$thirdColumnName = $request->get('thirdColumnName');
		$thirdColumnValues = $request->get('thirdColumnValues',[]);
		$lastColumnName = $thirdColumnName ? $thirdColumnName : $secondColumnName ;
	
		$startDate = $request->get('startDate');
		$totalColumnName = 'total_cost';
		$endDate = $request->get('endDate');
		$result = DB::table($tableName)
		->where('company_id',$company->id)
		->when($startDate && !$endDate, function (Builder $builder) use ($startDate) {
			$builder->where('date', '>=', $startDate);
		})
		->when($endDate && !$startDate, function (Builder $builder) use ($endDate) {
			$builder->where('date', '<=', $endDate);
		})->when($endDate &&  $startDate, function (Builder $builder) use ($startDate, $endDate) {
			$builder->whereBetween('date', [$startDate, $endDate]);
		})
		->whereIn($firstColumnName,$firstColumnValues)
		->when($thirdColumnName,function($query) use($secondColumnName,$secondColumnValues){
			$query->whereIn($secondColumnName,$secondColumnValues);
		})
		->groupBy($lastColumnName)
		->orderByRaw('sum('.$totalColumnName.') desc')
		->selectRaw('sum('.$totalColumnName.') as total , '.$lastColumnName.' as second_column')
		->get()->toArray();
		return response()->json([
			'status'=>true,
			'result'=>$result
		]);
	}
}
