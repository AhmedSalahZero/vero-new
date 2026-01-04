<?php
namespace App\Models\NonBankingService;


use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\ReadyFunctions\ConvertFlatRateToDecreasingRate;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class MicrofinanceProductSalesProject extends Model
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
            foreach ($model->flat_rates as $dateAsIndex => $flatRate) {
                $tenor = $model->tenor;
                $decreaseRates[$dateAsIndex] = $convertFlatRateToDecreasingRate->excel_rate($flatRate, $tenor);
            }
            $model->decrease_rates = $decreaseRates;
                
            // if ($study->isMonthlyStudy()) {
            //     $model->monthly_product_mixes = $model->product_mixes;
            //     $operationDates = range($study->getOperationStartDateAsIndex(), $study->getStudyEndDateAsIndex());
            //     $model->monthly_amounts = HArr::repeatThrough($model->avg_amount, $operationDates);
            // } else {
            //    $dateIndexWithDate = $study->getDateIndexWithDate();
          //      $yearsWithItsActiveMonths = $study->getYearIndexWithItsMonthsAsIndexAndString();
        //        $model->monthly_seasonality = (new SeasonalityService())->calculateSeasonalityPercentagePerMonth($model->seasonality, $yearsWithItsActiveMonths, $dateIndexWithDate);
                $model->monthly_product_mixes =$model->product_mixes;
                    
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
                //  $amount * (1+$currentIncreaseRate/100);
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
                
            // }
        });
          
    }
    protected $casts = [
        'increase_rates'=>'array',
        'product_mixes'=>'array',
        'monthly_product_mixes'=>'array',
        'seasonality'=>'array',
        'monthly_seasonality'=>'array',
        'flat_rates'=>'array',
        'fees_rates'=>'array',
        'setup_fees_durations'=>'array',
        'decrease_rates'=>'array',
        'monthly_amounts'=>'array',
        'monthly_loan_amounts'=>'array',
        'total_cases_counts'=>'array'
        ];
    public function getTenor():int
    {
		if($this->type =='by-branch'){
			return $this->study->microfinanceByBranchProductMixes->where('microfinance_product_id',$this->microfinance_product_id)->first()->getTenor();
		}
        return $this->tenor ;
    }
    public function getAvgAmount():float
    {
		if($this->type =='by-branch'){
			return $this->study->microfinanceByBranchProductMixes->where('microfinance_product_id',$this->microfinance_product_id)->first()->getAvgAmount();
		}
        return $this->avg_amount;
    }
	
	 public function getEarlyPaymentInstallmentCounts():int
    {
		if($this->type =='by-branch'){
			return $this->study->microfinanceByBranchProductMixes->where('microfinance_product_id',$this->microfinance_product_id)->first()->getEarlyPaymentInstallmentCounts();
		}
        return $this->early_payment_installment_counts;
    }
	
    public function getFundedBy():string
    {
		if($this->type =='by-branch'){
			return $this->study->microfinanceByBranchProductMixes->where('microfinance_product_id',$this->microfinance_product_id)->first()->getFundedBy();
		}
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
    public function getProductMixAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
        return $this->product_mixes[$yearOrDateIndex]??0;
    }
    public function getFlatRateAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
		if($this->type =='by-branch'){
			return $this->study->microfinanceByBranchProductMixes->where('microfinance_product_id',$this->microfinance_product_id)->first()->getFlatRateAtYearOrMonthIndex($yearOrDateIndex);
		}
        return $this->flat_rates[$yearOrDateIndex]??0;
    }  
	public function getSetupFeesRateAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
		// if($this->type =='by-branch'){
		// 	return $this->study->microfinanceByBranchProductMixes->where('microfinance_product_id',$this->microfinance_product_id)->first()->getFlatRateAtYearOrMonthIndex($yearOrDateIndex);
		// }
        return $this->fees_rates[$yearOrDateIndex]??1;
    }public function getSetupFeesDurationAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
		// if($this->type =='by-branch'){
		// 	return $this->study->microfinanceByBranchProductMixes->where('microfinance_product_id',$this->microfinance_product_id)->first()->getFlatRateAtYearOrMonthIndex($yearOrDateIndex);
		// }
        return $this->setup_fees_durations[$yearOrDateIndex]??12;
    }
    public function getIncreaseRateAtYearIndex($yearIndex)
    {
        return $this->increase_rates[$yearIndex] ?? 0;
    }
    // $monthIndex  01 for jan
    public function getSeasonalityOfMonthIndex($monthNumber)
    {
        return $this->seasonality[$monthNumber] ?? 0;
    }
	public function generateDecreasingRate(int $dateAsIndex)
	{
		$currentSetupFeesDuration =$this->getSetupFeesDurationAtYearOrMonthIndex($dateAsIndex) ;
		$currentSetupFeesRate = $this->getSetupFeesRateAtYearOrMonthIndex($dateAsIndex) ;
		$currentFlatRate = $this->getFlatRateAtYearOrMonthIndex($dateAsIndex) ;
		 $convertFlatRateToDecreasingRate = new ConvertFlatRateToDecreasingRate();
		$decreasedRate = $convertFlatRateToDecreasingRate->excel_rate($currentFlatRate,$this->tenor);
		$setupDecreaseRate = $convertFlatRateToDecreasingRate->excel_rate($currentFlatRate-$currentSetupFeesRate,$this->tenor); ;
		$result = [];
		$counter = 1 ;
		$dateIndexWithDate = $this->study->getDateIndexWithDate();
		
		for($i = $dateAsIndex ; $i< $this->tenor+$dateAsIndex ; $i++){
			$dateAsString = $dateIndexWithDate[$i]??null ;
			if(is_null($dateAsString)){
				continue;
			}
			if($counter<= $currentSetupFeesDuration){
				$result[$dateAsString] =$setupDecreaseRate ;
			}else{
				$result[$dateAsString] = $decreasedRate;
			}
			$counter++;
		}
		
		return $result;
	}
}
