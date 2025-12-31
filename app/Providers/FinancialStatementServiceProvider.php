<?php 
namespace App\Providers;

use App\Models\FinancialPlanning\Study;
use App\Models\IncomeStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FinancialStatementServiceProvider extends ServiceProvider
{
	public function register()
	{
		
	}
	public function boot(Request $request) 
	{
		$yearIndexWithYear = [];
		$dateIndexWithDate = [];
		$dateWithDateIndex = [];
		$studyStartDate = null;
		$studyEndDate = null;
		$incomeStatementId = Request()->segment(4);
		$incomeStatement = IncomeStatement::find($incomeStatementId);
		
		
		$requestSegments = Request()->segments() ;
		if($incomeStatement 
		&& 
		(
			in_array('forecast-report',$requestSegments) 
			|| in_array('actual-report',$requestSegments) 
			|| in_array('adjusted-report',$requestSegments) 
			|| in_array('modified-report',$requestSegments) 
		 )
		){
				$financialStatement = $incomeStatement->financialStatement ;
				$datesAndIndexesHelpers = $financialStatement->getDatesIndexesHelper();
				$datesIndexWithYearIndex=$datesAndIndexesHelpers['datesIndexWithYearIndex']; 
	
				$yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear']; 
				$dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate']; 
				$dateIndexWithMonthNumber=$datesAndIndexesHelpers['dateIndexWithMonthNumber']; 
				$dateWithMonthNumber=$datesAndIndexesHelpers['dateWithMonthNumber']; 
				$dateWithDateIndex=$datesAndIndexesHelpers['dateWithDateIndex']; 
				
				app()->singleton('datesIndexWithYearIndex',function() use ($datesIndexWithYearIndex){
					return $datesIndexWithYearIndex;
				});
				app()->singleton('yearIndexWithYear',function() use ($yearIndexWithYear){
					return $yearIndexWithYear;
				});
				app()->singleton('dateIndexWithDate',function() use ($dateIndexWithDate){
					return $dateIndexWithDate;
				});
				app()->singleton('dateWithMonthNumber',function() use ($dateWithMonthNumber){
					return $dateWithMonthNumber;
				});
				app()->singleton('dateIndexWithMonthNumber',function() use ($dateIndexWithMonthNumber){
					return $dateIndexWithMonthNumber;
				});
				app()->singleton('dateWithDateIndex',function() use ($dateWithDateIndex){
					return $dateWithDateIndex;
				});
				foreach([
					// [0 => '']
					'datesIndexWithYearIndex'=>$datesIndexWithYearIndex , 
					'yearIndexWithYear'=>$yearIndexWithYear 
				// ,'dateIndexWithDate'=>$dateIndexWithDate 
				// ,'dateWithMonthNumber'=>$dateWithMonthNumber
				// ,'dateIndexWithMonthNumber'=>$dateIndexWithMonthNumber
				// ,'dateWithDateIndex'=>$dateWithDateIndex
				 ] as $key => $dateArr){
					View::share($key,$dateArr);
				}
			
			
		}
	}
		
}
