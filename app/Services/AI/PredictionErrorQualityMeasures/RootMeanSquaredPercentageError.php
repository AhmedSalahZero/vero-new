<?php 
namespace App\Services\AI\PredictionErrorQualityMeasures;
class RootMeanSquaredPercentageError 
{
	function calculate($actual, $forecasted) {
		// Ensure both arrays have the same length
		if(count($actual) !== count($forecasted)) {
			dd('gggggg');
		}
	
		$n = count($actual);
		$sumSquaredPercentageError = 0;
	
		// Loop through each data point to calculate RMSPE
		for($i = 0; $i < $n; $i++) {
			if ($actual[$i] == 0) {
				// Avoid division by zero, this can be handled differently if needed
				continue;
			}
			// Calculate the squared percentage error
			$squaredPercentageError = pow(($actual[$i] - $forecasted[$i]) / $actual[$i], 2) * 100;
			$sumSquaredPercentageError += $squaredPercentageError;
		}
	
		// Calculate the mean squared error and then take the square root
		$meanSquaredError = $sumSquaredPercentageError / $n;
		$rmspe = sqrt($meanSquaredError);
		return $rmspe;
	}
	
}
