<?php 
namespace App\Services\AI;

use App\Helpers\HArr;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Services\AI\PredictionErrorQualityMeasures\MeanAbsoluteError;
use App\Services\AI\PredictionErrorQualityMeasures\MeanAbsolutePercentageError;
use App\Services\AI\PredictionErrorQualityMeasures\RootMeanSquaredPercentageError;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PredictSales
{
	public function predictByProphetMethod(Request $request, Company $company,$type,$endDate,array $predictionDates)
	{
		$startDate = Carbon::make($endDate)->startOfMonth()->subMonthNoOverflow(36)->format('Y-m-d') ;
		
        $data = [];
        $main_data = SalesGathering::company($request)
                                    ->whereBetween('date', [$startDate, $endDate])
									// ->where($type,'!=',null)
                                    // ->limit(10)
                                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date,DATE_FORMAT(date,"%Y") as year,net_sales_value,'.$type)->orderBy('date')
                                    ->get();
									
									// $totalsPerDates = $main_data->groupBy('gr_date')->map(function($sub_item){
									// 	return $sub_item->sum('net_sales_value');
									// })->toArray() ;
								
          
         		   $data = $main_data->groupBy($type)->map(function($year){
                            return $year->groupBy('gr_date')->map(function($sub_item){
                                return $sub_item->sum('net_sales_value');
                            });
                        })->toArray();
						foreach($data as $name => $dateValueArr){
							$predictAtDates = [];
							$training = $this->getTrainingDataFromOriginData($dateValueArr);
							$actual = $dateValueArr ;
							
							$actual = array_values($actual);
							$testCount = count($actual)-count($training) ;
							$yHat = [];
							$meanAbsoluteError = 0;
							$meanAbsolutePercentageError = 0;
							$rootMeanAbsolutePercentageError= 0;
							if(count($training)){
								$yHat =$this->forecastQualityValidation($training,$testCount+3);
								$predictionDates = collect($predictionDates)->map(function($item){
									return Carbon::make($item)->format('d-m-Y');
								})->toArray();
								
								// $meanAbsoluteError = (new MeanAbsoluteError)->calculate($actual,$yHat);
								// $meanAbsolutePercentageError=  (new MeanAbsolutePercentageError)->calculate($actual,$yHat);
								// $rootMeanAbsolutePercentageError=  (new RootMeanSquaredPercentageError)->calculate($actual,$yHat);
								$fullDates = array_merge(array_unique(array_merge(array_keys($dateValueArr),$predictionDates)));
								// if(count($fullDates) == count($yHat)){
								// 	$yHat = array_combine($fullDates,$yHat) ;
									
								// }
								// $yHat = array_combine(array_merge(array_unique(array_keys($dateValueArr),$predictionDates)),$yHat);
								// if($name == 'The Field'){
								// }
								foreach($predictionDates as $predictionDate){
									$predictionDateFormatted = Carbon::make($predictionDate)->format('d-m-Y');
									$predictAtDates[$predictionDate] = $yHat[$predictionDateFormatted]??0;
								}
							
							}
							
							
						
							$result[$name] = [
								'predictAtDates'=>$yHat ,
								'meanAbsoluteError'=>$meanAbsoluteError,
								'meanAbsolutePercentageError'=>$meanAbsolutePercentageError,
								'rootMeanAbsolutePercentageError'=>$rootMeanAbsolutePercentageError
							];
						
						}
						return $result;
						
					
						
						
						
	}
	/**
	 * * هنجيب 70 في الميه من الداتا وهنشيل اخر 30 في المية
	 */
	protected function getTrainingDataFromOriginData(array $originalArray):array 
	{
		$totalElements = count($originalArray);

// حساب عدد العناصر التي تمثل 30% من المصفوفة
$elementsToRemove = ceil($totalElements * 0.3); // استخدام ceil للتقريب للأعلى

// حساب عدد العناصر المتبقية بعد إزالة آخر 30%
$remainingElements = $totalElements - $elementsToRemove;

// إنشاء مصفوفة جديدة تحتوي على أول 70% من العناصر
$newArray = array_slice($originalArray, 0, $remainingElements);
return $newArray;
		
	}
	public function forecastQualityValidation(array $data,$testCount = 4)
	{
		$max = max($data) * 1.75;
		$dataFormatted = [];
		foreach($data as $date => $value){
			$year = Carbon::make($date)->format('Y');
			$month = Carbon::make($date)->format('m');
			$day = Carbon::make($date)->format('d');
			$date = $year . '-'.$month.'-'.$day;
			$dataFormatted['"ds"'][] = '"'.$date.'"' ;
			$dataFormatted['"y"'][] = $value ;
		}

		
		$pythonFilePath = resource_path('python/forecast/prophet_predicit.py');

		$dataFormatted = json_encode($dataFormatted);

		$x = shell_exec('python3 '. $pythonFilePath .' '. $dataFormatted  . ' ' . $max . ' ' . $testCount );
		
		preg_match('/\[(.*?)\]/s', $x, $matches);

		// Step 2: Remove any newlines and extra spaces
		$cleaned_data = preg_replace('/\s+/', ' ', $matches[1]);

		// Step 3: Split the cleaned data into an array of values
		$values = explode(' ', $cleaned_data);
		$values = collect($values)->filter(function($val){return is_numeric($val);})->values()->toArray();
		return $values ;
	}
	
	public function execute(Request $request, Company $company,$type,$endDate)
    {
		
        // enhanced in sales dashboard // salah
  
		$startDate = Carbon::make($endDate)->startOfMonth()->subMonthNoOverflow(36)->format('Y-m-d') ;
	
        $data = [];
       						 $main_data = SalesGathering::company($request)
                                    ->whereBetween('date', [$startDate, $endDate])
									// ->where($type,'!=',null)
                                    // ->limit(10)
                                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date,DATE_FORMAT(date,"%Y") as year,net_sales_value,'.$type)->orderBy('date')
                                    ->get();
									
									$totalsPerDates = $main_data->groupBy('gr_date')->map(function($sub_item){
										return $sub_item->sum('net_sales_value');
									})->toArray() ;
								
							
									
									$lastAndGrowthRateForCompanyArr = $this->getLastAndGrowthRateForCompany($totalsPerDates,$endDate );
									$growthRateForCompany = $lastAndGrowthRateForCompanyArr['growth_rate']??0;
									$next0ForecastForCompany = $lastAndGrowthRateForCompanyArr['next0ForecastForCompany']??0;
									$next1ForecastForCompany = $lastAndGrowthRateForCompanyArr['next1ForecastForCompany']??0;
									$next2ForecastForCompany = $lastAndGrowthRateForCompanyArr['next2ForecastForCompany']??0;
									$next3ForecastForCompany = $lastAndGrowthRateForCompanyArr['next3ForecastForCompany']??0;
						
          
         		   $data = $main_data->groupBy($type)->map(function($year){
                            return $year->groupBy('gr_date')->map(function($sub_item){
                                return $sub_item->sum('net_sales_value');
                            });
                        })->toArray();
					
				
				
						$lastAndGrowthRateForItems = [];	
						
       foreach($data as $name => $dataItem){
		$currentResult = $this->getLastAndGrowthRateForItem($dataItem,$endDate,$growthRateForCompany,$type,$name) ;

		if(!is_null($currentResult)){
			$lastAndGrowthRateForItems[$name]=$currentResult;
		}
	}
	  
	 	  uasort($lastAndGrowthRateForItems, function ($a, $b) {
				return $b['next0ForecastForItem'] <=> $a['next0ForecastForItem'];
		});

		return [
			'for_item'=>$lastAndGrowthRateForItems,
			'for_company'=>[
				'next0ForecastForCompany'=>$next0ForecastForCompany,
				'next1ForecastForCompany'=>$next1ForecastForCompany,
				'next2ForecastForCompany'=>$next2ForecastForCompany,
				'next3ForecastForCompany'=>$next3ForecastForCompany,
			]
		];
       
    }
	
	protected function getLastAndGrowthRateForCompany(array $totalsPerDates,string $endDate  ):array 
	{
		// Get the last 12 keys
		$last12Items = HArr::sliceWithDates($totalsPerDates , $endDate,11);
		$slicedItems = $totalsPerDates ;
		foreach($last12Items as $key => $value){
			unset($slicedItems[$key]);
		}
		$previousOfPrevious12Items = HArr::sliceWithDates($slicedItems , $endDate,23) ;
		$last12ItemsCounter = count($last12Items);
		$sumOfLast6Months = array_sum(HArr::sliceWithDates($last12Items , $endDate,5));
		
		$previousOfPrevious12ItemsCounter = count($previousOfPrevious12Items);
		$last12ItemsAvg = $last12ItemsCounter ? array_sum($last12Items) / $last12ItemsCounter : 0;
		$previousOfPrevious12ItemsAvg =$previousOfPrevious12ItemsCounter ? array_sum($previousOfPrevious12Items) /  $previousOfPrevious12ItemsCounter : 0;
		$growthRateForCompany = $previousOfPrevious12ItemsAvg ? ($last12ItemsAvg / $previousOfPrevious12ItemsAvg) - 1 : 0 ;
		$next1Month = Carbon::make($endDate)->addMonthsNoOverflow(1)->format('Y-m-d');
		$next2Month = Carbon::make($endDate)->addMonthsNoOverflow(2)->format('Y-m-d');
		$next3Month = Carbon::make($endDate)->addMonthsNoOverflow(3)->format('Y-m-d');
		$valueOfMonth = HArr::getValueFromMonthAndYear($last12Items,Carbon::make($endDate)->format('m'),Carbon::make($endDate)->format('Y')) ;
		$next1MonthPercentageValueAtMonth = HArr::getValueFromMonth($last12Items,Carbon::make($next1Month)->format('m'));
		$next1MonthPercentage = array_sum($last12Items) && $sumOfLast6Months != 0  ? $next1MonthPercentageValueAtMonth / array_sum($last12Items) : 0;
	
		$next2MonthPercentageValueAtMonth = HArr::getValueFromMonth($last12Items,Carbon::make($next2Month)->format('m'));
		$next2MonthPercentage = array_sum($last12Items) && $sumOfLast6Months != 0  ? $next2MonthPercentageValueAtMonth / array_sum($last12Items) : 0;
		
		$next3MonthPercentageValueAtMonth = HArr::getValueFromMonth($last12Items,Carbon::make($next3Month)->format('m'));
		$next3MonthPercentage = array_sum($last12Items) && $sumOfLast6Months != 0  ? $next3MonthPercentageValueAtMonth / array_sum($last12Items) : 0;
		
		$next1ForecastForCompany = ($last12ItemsAvg*12) * (1+$growthRateForCompany) * $next1MonthPercentage;
		$next2ForecastForCompany = ($last12ItemsAvg*12) * (1+$growthRateForCompany) * $next2MonthPercentage;
		$next3ForecastForCompany = ($last12ItemsAvg*12) * (1+$growthRateForCompany) * $next3MonthPercentage;
	
		return [
			
			'growth_rate'=>$growthRateForCompany,
			'next0ForecastForCompany'=>$valueOfMonth,
			'next1ForecastForCompany'=>$next1ForecastForCompany,
			'next2ForecastForCompany'=>$next2ForecastForCompany,
			'next3ForecastForCompany'=>$next3ForecastForCompany,
		];
	}
	
	protected function getLastAndGrowthRateForItem(array $totalsPerDates,string $endDate  ,$grForCompany ,$type , $currentItemName):?array 
	{
		// Get the last 12 keys
		$last12Items = HArr::sliceWithDates($totalsPerDates , $endDate,11);
		$slicedItems = $totalsPerDates ;
	
		foreach($last12Items as $key => $value){
			unset($slicedItems[$key]);
		}
		$previousOfPrevious12Items = HArr::sliceWithDates($slicedItems , $endDate,23) ;
		$last12ItemsCounter = count($last12Items);
		if($last12ItemsCounter < 12){
			return null;
		}
		$sumOfLast6Months = array_sum(HArr::sliceWithDates($last12Items , $endDate,5));
		
		if($sumOfLast6Months == 0){
			return null;
		}
		$previousOfPrevious12ItemsCounter = count($previousOfPrevious12Items);
		$last12ItemsAvg = $last12ItemsCounter ? array_sum($last12Items) / $last12ItemsCounter : 0;
		
		$previousOfPrevious12ItemsAvg =$previousOfPrevious12ItemsCounter ? array_sum($previousOfPrevious12Items) /  $previousOfPrevious12ItemsCounter : 0;
		$growthRateForItem = $previousOfPrevious12ItemsAvg ? ($last12ItemsAvg / $previousOfPrevious12ItemsAvg) - 1 : 0 ;
		$next1Month = Carbon::make($endDate)->addMonthsNoOverflow(1)->format('Y-m-d');
		$next2Month = Carbon::make($endDate)->addMonthsNoOverflow(2)->format('Y-m-d');
		$next3Month = Carbon::make($endDate)->addMonthsNoOverflow(3)->format('Y-m-d');
		
		
		$valueOfMonth = HArr::getValueFromMonthAndYear($last12Items,Carbon::make($endDate)->format('m'),Carbon::make($endDate)->format('Y')) ;
		
		$next1MonthPercentageValueAtMonth = HArr::getValueFromMonth($last12Items,Carbon::make($next1Month)->format('m'));
		$next1MonthPercentage = array_sum($last12Items) && $sumOfLast6Months != 0  ? $next1MonthPercentageValueAtMonth / array_sum($last12Items) : 0;

		$next2MonthPercentageValueAtMonth = HArr::getValueFromMonth($last12Items,Carbon::make($next2Month)->format('m'));
		$next2MonthPercentage = array_sum($last12Items) && $sumOfLast6Months != 0  ? $next2MonthPercentageValueAtMonth / array_sum($last12Items) : 0;
		
		$next3MonthPercentageValueAtMonth = HArr::getValueFromMonth($last12Items,Carbon::make($next3Month)->format('m'));
		$next3MonthPercentage = array_sum($last12Items) && $sumOfLast6Months != 0  ? $next3MonthPercentageValueAtMonth / array_sum($last12Items) : 0;
		
	
		$itemForecastGrowthRate = 0 ;
		if($grForCompany < $growthRateForItem){
			$itemForecastGrowthRate = ($grForCompany + $growthRateForItem) / 2 ; 
		}elseif($growthRateForItem == 0){
			$itemForecastGrowthRate = $grForCompany ; 
		}
		else{
			$itemForecastGrowthRate = $growthRateForItem;
		}
				$next1ForecastForItem = ($last12ItemsAvg*12) * (1+$itemForecastGrowthRate) * $next1MonthPercentage;
				$next1ForecastForItem = $next1ForecastForItem < 0 ? 0 : $next1ForecastForItem ;
				
				
				
				
				$next2ForecastForItem = ($last12ItemsAvg*12) * (1+$itemForecastGrowthRate) * $next2MonthPercentage;
				$next2ForecastForItem = $next2ForecastForItem < 0 ? 0 : $next2ForecastForItem ;
				$next3ForecastForItem = ($last12ItemsAvg*12) * (1+$itemForecastGrowthRate) * $next3MonthPercentage;
				$next3ForecastForItem = $next3ForecastForItem < 0 ? 0 : $next3ForecastForItem ;
			
			
	
		return [
			// 'last_12_avg'=>$last12ItemsAvg ,
			// 'last_24_avg'=>$previousOfPrevious12ItemsAvg ,
			// 'growth_rate'=>$growthRateForCompany,
			// 'next1MonthPercentage'=>$next1MonthPercentage,
			// 'next2MonthPercentage'=>$next2MonthPercentage,
			// 'next3MonthPercentage'=>$next3MonthPercentage,
			'next0ForecastForItem'=>$valueOfMonth,
			'next1ForecastForItem'=>$next1ForecastForItem,
			'next2ForecastForItem'=>$next2ForecastForItem,
			'next3ForecastForItem'=>$next3ForecastForItem,
		
			
			
		];
	}
}
