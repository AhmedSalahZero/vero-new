<?php 
namespace App\Services\AI;

use Phpml\Regression\LeastSquares;


class SimpleLinearRegression
{
	public static function predict(array $trainingSet , array $predictionDates,string $breakdownEndDate,$type)
	{
		$predictedSales = [];
		foreach($trainingSet as $nameForType => $itemsAsDateAndValue){
			$index = 0 ;
			foreach($predictionDates as $predictionDate){
					$salesData = [];
				foreach($itemsAsDateAndValue as $currentDate => $currentSalesValue){
					$salesData[] = [
						'timestamp'=>[strtotime($currentDate)] ,
						'total_sales'=>[$currentSalesValue]
					] ;	
				}
			// Extract timestamps and sales values
			$timestamps = array_column($salesData, 'timestamp');
			$sales = array_column($salesData, 'total_sales');
			

			// Train the regression model
			$regression = new LeastSquares();
			// $regression = new SVR(Kernel::LINEAR);
			if(!isset($sales[1])){
				$predictedSales[$nameForType][$predictionDate] = 0;
			}else{
				$regression->train($timestamps,$sales);
				$futureTimestamp = strtotime($predictionDate);
				if($index == 0){
					$predictedSales[$nameForType][$breakdownEndDate] = $trainingSet[$nameForType][$breakdownEndDate] ?? 0;
				}else{
					$predictedValue = $regression->predict([$futureTimestamp]) ;
					$predictedSales[$nameForType][$predictionDate] = $predictedValue < 0 ? 0 : round($predictedValue);
				}
				$index ++ ;
				}
			
			
		}
		
		
		
	
	}
		
		return self::orderByFirstDate($predictedSales,$breakdownEndDate,$predictionDates);
		
	}
	public static function  orderByFirstDate($items,$breakdownEndDate,$predictionDates){
		$data = collect($items)->sortByDesc($breakdownEndDate)
		->take(100)
		->toArray() ;
		$totals = [];
		foreach($predictionDates as $date){
			$totals[$date] = array_sum(array_column($data,$date));
		}
		$data['total'] = $totals;
		return $data;
	}
	
}
