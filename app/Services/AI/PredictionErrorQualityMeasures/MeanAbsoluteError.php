<?php 
namespace App\Services\AI\PredictionErrorQualityMeasures;
class MeanAbsoluteError 
{
	function calculate(array $actual, array $predicted): float {
		// Check if both arrays have the same length
		// if (count($actual) !== count($predicted)) {
		// }
	
		$n = count($actual);
		$absoluteErrorSum = 0;
	
		// Calculate the sum of absolute differences
		for ($i = 0; $i < $n; $i++) {
			$absoluteErrorSum += abs($actual[$i] - $predicted[$i]);
		}
	
		// Calculate and return MAE
		return $absoluteErrorSum / $n;
	}
}
