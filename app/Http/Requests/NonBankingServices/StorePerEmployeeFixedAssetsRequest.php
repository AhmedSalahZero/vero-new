<?php

namespace App\Http\Requests\NonBankingServices;

use App\Equations\MonthlyFixedRepeatingAmountEquation;
use App\Helpers\HArr;
use App\Models\NonBankingService\Manpower;
use App\Models\NonBankingService\Position;
use App\Models\NonBankingService\Study;
use App\Rules\PositionMustExistIfAmountGreaterThanZeroRule;
use Arr;
use Illuminate\Foundation\Http\FormRequest;

class StorePerEmployeeFixedAssetsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
	public function prepareForValidation()
	{
	
		$fixedAssets = $this->get('fixedAssets');
		$fixedAssetType = $this->get('fixed_asset_type');
		$studyId =$this->study_id;
		
		$companyId = $this->route('company')->id;
		$study = Study::find($studyId);
		/**
		 * @var Study $study 
		 */
	//	$operationStartDateFormatted = $study->getOperationStartDateFormatted();
		$dateWithDateIndex = $study->getDateWithDateIndex();
		// $operationStartDateAsIndex = $this->getOperationStartDateAsIndex($dateWithDateIndex,$operationStartDateFormatted);
		$studyEndDateAsString = $study->getStudyEndDate();
		$studyStartDateAsString = $study->getStudyStartDate();
		$studyStartDateAsIndex = $study->getStudyStartDateAsIndex($dateWithDateIndex,$studyStartDateAsString);
		$studyEndDateAsIndex = $study->getStudyEndDateAsIndex($dateWithDateIndex,$studyEndDateAsString);
	
		$totalFixedAssetAmounts = [];
		$currentFixedAssetAmounts = [];
		foreach($fixedAssets as $rowIndex => &$fixedAssetArr){
			$currentFixedAmount = $fixedAssetArr['ffe_item_cost']??0;
			if($currentFixedAmount == 0){
				unset($fixedAssets[$rowIndex]);
			}
			$fixedAssetArr['ffe_counts'] =$fixedAssetArr['ffe_counts'] ? (array)json_decode($fixedAssetArr['ffe_counts']) : [];
			$fixedAssetArr['type'] =$fixedAssetType;
			$fixedAssetArr['due_days'] = array_unique($fixedAssetArr['due_days']??[]);
            foreach ($fixedAssetArr['due_days']??[] as $index => $dueDay) {
                $paymentRate = $fixedAssetArr['payment_rate'][$index];
                $fixedAssetArr['custom_collection_policy'][$dueDay] = isset($fixedAssetArr['custom_collection_policy'][$dueDay]) ? $fixedAssetArr['custom_collection_policy'][$dueDay]+ $paymentRate :$paymentRate;
            }
			$currentPositionIds  = $fixedAssetArr['position_ids']??[];
			$currentPositions = Manpower::where('study_id',$study->id)->whereIn('position_id',$currentPositionIds)->get();
			$hiringCountArrs = $currentPositions->pluck('hiring_counts')->toArray();
			$dates = array_keys(Arr::first($hiringCountArrs,null,[]));
			$sumHiringCount = HArr::sumAtDates($hiringCountArrs,$dates);
			
			$itemCost = $fixedAssetArr['ffe_item_cost']??0;
			$vatRate = $fixedAssetArr['vat_rate']??0;
			$isDeductible = false;
			$increaseRate = $fixedAssetArr['cost_annual_increase_rate']??0;
			$withholdRate = $fixedAssetArr['withhold_tax_rate']??0;
			$currentFfeItemCostPerDateIndex = (new MonthlyFixedRepeatingAmountEquation())->calculate($itemCost,$studyStartDateAsIndex,$studyEndDateAsIndex,'annually',$increaseRate,$isDeductible,$vatRate,$withholdRate);
			
			foreach($sumHiringCount as $dateAsIndex => $hiringValue){
				$currentFixedAssetAmounts[$rowIndex][$dateAsIndex]  = $hiringValue * ($currentFfeItemCostPerDateIndex[$dateAsIndex]??0);
				$totalFixedAssetAmounts[$dateAsIndex] = isset($totalFixedAssetAmounts[$dateAsIndex]) ? $totalFixedAssetAmounts[$dateAsIndex] +  $currentFixedAssetAmounts[$rowIndex][$dateAsIndex] : $currentFixedAssetAmounts[$rowIndex][$dateAsIndex];
				
			}
		}
		
		$this->merge([
			'fixedAssets'=>$fixedAssets ,
			'perEmployeeFixedAssetsFundingStructure'=>[
				'direct_ffe_amounts'=>$totalFixedAssetAmounts
			]
		]);
	}
    public function rules()
    {
        return [
			'fixedAssets'=>[new PositionMustExistIfAmountGreaterThanZeroRule()]
        ];
    }
}
