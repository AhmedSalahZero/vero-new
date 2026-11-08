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
		// SECURITY: table and column names come from the request and reach raw SQL
		// (selectRaw / groupBy). Only the analysis tables and real identifiers are
		// accepted (GuardAnalysisSqlInput enforces the same rules globally).
		abort_unless(in_array($tableName, \App\Http\Middleware\GuardAnalysisSqlInput::ALLOWED_TABLES, true), 422);
		foreach (['mainColumnName', 'secondColumnName', 'thirdColumnName'] as $columnKey) {
			$columnValue = $request->get($columnKey);
			abort_unless($columnValue === null || $columnValue === '' || (is_string($columnValue) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $columnValue)), 422);
		}
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
