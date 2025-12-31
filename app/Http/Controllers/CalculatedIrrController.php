<?php

namespace App\Http\Controllers;



use Auth;
use function PHPSTORM_META\map;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

use Session;

class CalculatedIrrController extends Controller
{
	// public static  function calculateIrr($yearsAndFreeCash, $calculatedPercentage = null, $numberOfIteration = 1)
	// {



	// 	if ($numberOfIteration == 1 && (checkIfArrayAllIsAllNegative($yearsAndFreeCash) || checkIfArrayAllIsAllPositive($yearsAndFreeCash))) {
	// 		return 'No IRR';
	// 	}

	// 	$percentage = $calculatedPercentage;
	// 	$discountFactory = [];
	// 	$npv = [];
	// 	$yearsAndFreeCash = array_values($yearsAndFreeCash);
	// 	foreach ($yearsAndFreeCash as $year => $freshCash) {
	// 		$year = $year + 1;
	// 		$discountFactory[$year] = pow(1  +  $percentage, $year);
	// 		$npv[$year] = $freshCash / $discountFactory[$year];
	// 	}

	// 	$npv_sum = array_sum($npv);
	// 	if ($numberOfIteration == 750000) {
	// 		return $calculatedPercentage;
	// 	}
	// 	// need to make $npv_sum = 0 by changing  $percentage  to get irr 
	// 	while ($npv_sum > $npv_sum * 0.000001 || $npv_sum < $npv_sum * -0.000001) {
	// 		if ($npv_sum > 0) {
	// 			$irr = $percentage  + 0.0001;
	// 			return self::calculateIrr($yearsAndFreeCash, $irr, ++$numberOfIteration);
	// 		} else {
	// 			$irr = $percentage - 0.00001;
	// 			return self::calculateIrr($yearsAndFreeCash, $irr, ++$numberOfIteration);
	// 		}
	// 	}

	// 	return $calculatedPercentage;
	// }
}
