<?php
namespace App\Models\NonBankingService;

use App\Equations\MonthlyFixedRepeatingAmountEquation;
use App\Helpers\HArr;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\ReadyFunctions\ConvertFlatRateToDecreasingRate;
use App\ReadyFunctions\SeasonalityService;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class ConsumerfinanceProductSalesProject extends Model
{
    use HasBasicStoreRequest,CompanyScope , BelongsToStudy;
    protected $connection= 'non_banking_service';

    protected $guarded = ['id'];
    public static function boot()
    {
        parent::boot();
        static::saving(function (self $model) {
            $study = $model->study ;
            $decreaseRates =[];
            $convertFlatRateToDecreasingRate = new ConvertFlatRateToDecreasingRate();
            foreach ($model->flat_rates as $dateAsIndex => $value) {
                $tenor = $model->tenor;
                $decreaseRates[$dateAsIndex] = $convertFlatRateToDecreasingRate->excel_rate($value, $tenor);
            }
            $model->decrease_rates = $decreaseRates;
            if ($study->isMonthlyStudy()) {
                $operationDates = range($study->getOperationStartDateAsIndex(), $study->getStudyEndDateAsIndex());
                $model->monthly_amounts = HArr::repeatThrough($model->avg_amount, $operationDates);
                
            } else {
                    
                $operationStartDateAsIndex = $study->getOperationStartDateAsIndex() ;
                $operationEndDateAsIndex = $study->getStudyEndDateAsIndex();
                $currentStartDateAsIndex = $operationStartDateAsIndex;
                $endDateAsIndex = $operationEndDateAsIndex;
                $operationDates = range($operationStartDateAsIndex, $operationEndDateAsIndex);
                $increaseRates = $model->increase_rates ?:[];
                $dateIndexWithYearIndex = $study->getDatesIndexWithYearIndex();
                $intervalMode =12 ;
                $counter = 0 ;
                $resultWithoutVat = [];
                $amountBeforeVat = $model->avg_amount;
                for ($currentStartDateAsIndex ; $currentStartDateAsIndex <= $endDateAsIndex ; $currentStartDateAsIndex++) {
                    $currentIncreaseRate = $increaseRates[$dateIndexWithYearIndex[$currentStartDateAsIndex]]??0  ;
                    if ($counter!=0&&$counter % $intervalMode == 0) {
                        $resultWithoutVat[$currentStartDateAsIndex] = $resultWithoutVat[$currentStartDateAsIndex-1] * (1+$currentIncreaseRate/100)  ;
                    } else {
                        if (!isset($resultWithoutVat[$currentStartDateAsIndex-1])) {
                            $resultWithoutVat[$currentStartDateAsIndex] = $amountBeforeVat  ;
                        } else {
                            $resultWithoutVat[$currentStartDateAsIndex] = $resultWithoutVat[$currentStartDateAsIndex-1]  ;
                        }
                    }
                    $counter++;
                }
                $model->monthly_amounts = $resultWithoutVat;
                
            }
        });
          
    }
    protected $casts = [
        'increase_rates'=>'array',
        'loan_amounts'=>'array',
        'monthly_loan_amounts'=>'array',
        'seasonality'=>'array',
        'monthly_seasonality'=>'array',
        'flat_rates'=>'array',
        'decrease_rates'=>'array',
        'monthly_amounts'=>'array',
        'loan_amounts'=>'array'
        ];
    public function getTenor():int
    {
	
        return $this->tenor ;
    }
    public function getAvgAmount():float
    {
        return $this->avg_amount;
    }
    public function getFundedBy():string
    {
		
        return $this->funded_by ;
    }
    public function getFundedByFormatted():string
    {
        $fundedBy = $this->getFundedBy();
        foreach (getMicrofinanceFundingBySelector() as $arr) {
            if ($arr['value'] == $fundedBy) {
                return $arr['title'];
            }
        }
		return __('N/A');
    }
    public function getSalesRevenuePayloadAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
        return $this->loan_amounts[$yearOrDateIndex]??0;
    }
	public function getMonthlyLoanAmountsAttribute()
	{
		return $this->loan_amounts;
	}
    public function getFlatRateAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
        return $this->flat_rates[$yearOrDateIndex]??0;
    }
    public function getIncreaseRateAtYearIndex($yearIndex)
    {
        return $this->increase_rates[$yearIndex] ?? 0;
    }

}
