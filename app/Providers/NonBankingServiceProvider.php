<?php 
namespace App\Providers;

use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NonBankingServiceProvider extends ServiceProvider
{
	public function register()
	{
		
	}
	public function boot(Request $request) 
	{
		$yearIndexWithYear = [];
		$dateIndexWithDate = [];
		$dateWithDateIndex = [];
		
		$studyId = getStudyIdFromUrl();
		
		if(is_numeric($studyId) && in_array(NON_BANKING_SERVICE_URL_PREFIX,Request()->segments())){
			$study = Study::find($studyId);
			/**
			 * @var Study $study 
			 */
			if($study){
				$datesAndIndexesHelpers = $study->getDatesIndexesHelper();
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
				,'dateIndexWithDate'=>$dateIndexWithDate 
				// ,'dateWithMonthNumber'=>$dateWithMonthNumber
				// ,'dateIndexWithMonthNumber'=>$dateIndexWithMonthNumber
				// ,'dateWithDateIndex'=>$dateWithDateIndex
				 ] as $key => $dateArr){
					View::share($key,$dateArr);
				}
			}
			
		}
	}
		
}
