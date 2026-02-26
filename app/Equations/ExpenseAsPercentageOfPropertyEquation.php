<?php
namespace App\Equations;

use App\Helpers\HArr;
use App\Models\Contract;
use App\Models\NonBankingService\Expense;
use App\Models\PropertyManagement\ForecastedProperty;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyContractFullRentRenewal;
use App\Models\PropertyManagement\PropertyContractPartialRentRenewal;
use App\Models\PropertyManagement\PropertyToBeDelivered;
use App\Models\PropertyManagement\Study;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ExpenseAsPercentageOfPropertyEquation
{
    public function calculate(int $studyId, string $percentageOf, array $revenueStreamType, array $categoryIds, int $startDateAsIndex, int $endDateAsIndex, float $monthlyRate, string $paymentTermType, float $vatRate, bool $isDeductible, float $withholdTaxRate, bool $isSensitivity = false):array
    {
        $study = Study::find($studyId);
        


        $dates = range($startDateAsIndex, $endDateAsIndex);
        $resultArrs = [];
        $result = [];
                
        
        
        $vats = [];
        $withholds = [];
       
        $formattedResult = [];
        $columnName = $percentageOf == 'revenue' ? 'rent_revenues' : 'rent_collections';
        $resultArrs = DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('property_contract_partial_rent_renewals')
        ->where('study_id', $studyId)
        ->pluck($columnName)->map(function ($item) {
            return (array)json_decode($item);
        })->toArray();
        foreach ($resultArrs as $resultArrItem) {
            foreach ($resultArrItem as $monthIndex => $val) {
                $formattedResult[$monthIndex] = isset($formattedResult[$monthIndex]) ? $formattedResult[$monthIndex] + $val : $val;
            }
        }
		 PropertyContractFullRentRenewal::getFullRentCoveragesAmounts($study, $columnName, $formattedResult);
		 PropertyContractPartialRentRenewal::getPartialRentCoveragesAmounts($study, $columnName, $formattedResult);
		 PropertyToBeDelivered::getToBeDeliveredCoveragesAmounts($study, $columnName, $formattedResult);
		 ForecastedProperty::getForecastedPropertiesCoveragesAmounts($study, $columnName, $formattedResult);
  
        foreach ($formattedResult as $monthIndex => $val) {
            $valBeforeRate = $monthlyRate / 100 * $val ;
            $valueAfterVat =  0;
            if (!$isDeductible) {
                $valueAfterVat = $valBeforeRate * (1+($vatRate/100));
            }
            $result[$monthIndex] = isset($result[$monthIndex]) ? $result[$monthIndex] + $valBeforeRate : $valBeforeRate ;
            $onlyVatValue = $valueAfterVat -$valBeforeRate ;
            $withholdValue = $withholdTaxRate / 100 * $valBeforeRate ;
            $vats[$monthIndex] = isset($vats[$monthIndex]) ? $vats[$monthIndex] + $onlyVatValue : $onlyVatValue;
            $withholds[$monthIndex] = isset($withholds[$monthIndex]) ? $withholds[$monthIndex] + $withholdValue : $withholdValue;
                    
        }
       
        $totalWithoutVat = [];
        foreach ($result as $monthIndex=>$value) {
            if ($monthIndex>= $startDateAsIndex && $monthIndex <= $endDateAsIndex) {
                $totalWithoutVat[$monthIndex] = isset($totalWithoutVat[$monthIndex]) ? $totalWithoutVat[$monthIndex] + $value : $value;
            }
        }
        $totalVat = [];
        foreach ($vats as $monthIndex=>$value) {
            if ($monthIndex>= $startDateAsIndex && $monthIndex <= $endDateAsIndex) {
                $totalVat[$monthIndex] = isset($totalVat[$monthIndex]) ? $totalVat[$monthIndex] + $value : $value;
            }
        }
        
        $totalWithhold = [];
        foreach ($withholds as $monthIndex=>$value) {
            if ($monthIndex>= $startDateAsIndex && $monthIndex <= $endDateAsIndex) {
                $totalWithhold[$monthIndex] = isset($totalWithhold[$monthIndex]) ? $totalWithhold[$monthIndex] + $value : $value;
            }
        }
        
        return [
            'total_withhold'=>$totalWithhold ,
            'total_before_vat'=>$totalWithoutVat ,
            'total_vat'=>$totalVat,
            'total_after_vat'=>HArr::sumAtDates([$totalWithoutVat,$totalVat], $dates)
        ];
    }
}
