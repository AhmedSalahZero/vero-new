<?php 
namespace App\Services\AI\PredictionErrorQualityMeasures;
class MeanAbsolutePercentageError 
{

public function calculate($actual, $forecasted) {
    // Ensure both arrays have the same length
    // if(count($actual) !== count($forecasted)) {
    // }

    $n = count($actual);
    $sumAbsPercentageError = 0;

    // Loop through each data point to calculate MAPE
    for($i = 0; $i < $n; $i++) {
        if ($actual[$i] == 0) {
            // Avoid division by zero, this can be handled differently if needed
            continue;
        }
        $absPercentageError = abs(($actual[$i] - $forecasted[$i]) / $actual[$i]) * 100;
        $sumAbsPercentageError += $absPercentageError;
    }

    // Calculate the mean MAPE
    $mape = $sumAbsPercentageError / $n;
    return $mape;
}

}
