<?php

namespace App\Models\PropertyManagement;

use App\Equations\MonthlyFixedRepeatingAmountEquation;
use App\Helpers\HArr;
use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;

use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $name_id
 * @property string $type
 * @property numeric $ffe_item_cost
 * @property numeric|null $vat_rate
 * @property numeric|null $withhold_tax_rate
 * @property numeric $contingency_rate
 * @property numeric $cost_annual_increase_rate
 * @property string|null $payment_terms
 * @property array<array-key, mixed>|null $custom_collection_policy
 * @property int $depreciation_duration
 * @property numeric $replacement_cost_rate
 * @property int $replacement_interval
 * @property int $counts
 * @property array<array-key, mixed>|null $ffe_counts
 * @property array<array-key, mixed>|null $monthly_amounts
 * @property int $company_id
 * @property int $study_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $department_ids
 * @property array<array-key, mixed>|null $position_ids
 * @property array<array-key, mixed>|null $statement
 * @property array<array-key, mixed>|null $ffe_equity_payment
 * @property array<array-key, mixed>|null $ffe_loan_withdrawal
 * @property array<array-key, mixed>|null $loan_capitalized_interests
 * @property array<array-key, mixed>|null $income_statement_loan_capitalized_interests
 * @property array<array-key, mixed>|null $ffe_loan_withdrawal_end_balance
 * @property array<array-key, mixed>|null $depreciation_statement
 * @property array<array-key, mixed>|null $total_monthly_depreciations
 * @property array<array-key, mixed>|null $capitalization_statement
 * @property array<array-key, mixed>|null $ffe_execution_and_payment
 * @property array<array-key, mixed>|null $ffe_payable
 * @property array<array-key, mixed>|null $ffe_payment
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\FixedAssetName|null $fixedAssetName
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereCapitalizationStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereContingencyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereCostAnnualIncreaseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereCounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereCustomCollectionPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereDepartmentIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereDepreciationDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereDepreciationStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereFfeCounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereFfeEquityPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereFfeExecutionAndPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereFfeItemCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereFfeLoanWithdrawal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereFfeLoanWithdrawalEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereFfePayable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereFfePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereIncomeStatementLoanCapitalizedInterests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereLoanCapitalizedInterests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereMonthlyAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereNameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset wherePositionIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereReplacementCostRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereReplacementInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereTotalMonthlyDepreciations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereVatRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAsset whereWithholdTaxRate($value)
 * @mixin \Eloquent
 */
class FixedAsset extends Model
{
    use BelongsToStudy,BelongsToCompany;
    protected $guarded = ['id'];
    protected $connection ='property_management';
    public const FFE = 'ffe';
    public const PER_EMPLOYEE = 'per-employee';
    // public const NEW_BRANCH = 'new-branch';
    protected $casts = [
        'ffe_counts'=>'array',
        'monthly_amounts'=>'array',
        'position_ids'=>'array',
        'department_ids'=>'array',
        'statement'=>'array',
        'ffe_equity_payment'=>'array',
        'ffe_loan_withdrawal'=>'array',
        'loan_capitalized_interests'=>'array',
        'income_statement_loan_capitalized_interests'=>'array',
        'ffe_loan_withdrawal_end_balance'=>'array',
        'depreciation_statement'=>'array',
        'capitalization_statement'=>'array',
        'ffe_execution_and_payment'=>'array',
        'ffe_payable'=>'array',
        'ffe_payment'=>'array',
        'custom_collection_policy'=>'array',
        'total_monthly_depreciations'=>'array',
    ];
    public function getId()
    {
        return $this->id;
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function model()
    {
        $modelName = '\App\Models\\'.$this->model_name ;
        return $this->belongsTo($modelName, 'model_id', 'id');
    }
    public function getNameId():int
    {
        return $this->name_id ;
    }
    public function fixedAssetName():BelongsTo
    {
        return $this->belongsTo(FixedAssetName::class, 'name_id', 'id');
    }
    public function getName():string
    {
        return $this->fixedAssetName ? $this->fixedAssetName->getName() : __('N/A');
    }
    // public function getName()
    // {
    // 	return $this->name ;
    // }
    public function getType():string
    {
        return $this->type;
    }
    public function isGeneral():bool
    {
        return $this->getType() == Self::FFE;
    } public function isPerEmployee():bool 
    {
        return $this->getType() == Self::PER_EMPLOYEE;
    }
	//  public function isPerNewBranch():bool 
    // {
    //     return $this->getType() == Self::NEW_BRANCH;
    // }
    public function getVatRate():float
    {
        return $this->vat_rate ?: 0;
    }
    public function getWithholdTaxRate():float 
    {
        return $this->withhold_tax_rate?:0;
    }
    public function getContingencyRate():float
    {
        return $this->contingency_rate?:0;
    }
    public function getDepreciationDuration():int
    {
        return $this->depreciation_duration ;
    }
    public function getDepreciationDurationInMonths():int
    {
        return $this->getDepreciationDuration() * 12 ;
    }
    public function getPaymentTerm()
    {
        return $this->payment_terms ;
    }
    public function getReplacementInterval()
    {
        return $this->replacement_interval ;
    }
    public function getReplacementIntervalInMonths():int
    {
        return $this->getReplacementInterval() * 12 ;
    }
    public function getMonthlyAmounts():array
    {
        return (array)$this->monthly_amounts;
    }
    public function getCount():int
    {
        return $this->counts;
    }
    public function getPurchaseDates(array $dateIndexWithDate):array
    {
        // $dateAsIndexString = app('dateIndexWithDate');
        
        $dates= [];
        $ffeCounts = $this->getFfeCounts();
        foreach ($ffeCounts as $dateAsIndex => $ffeCount) {
            if ($ffeCount > 0) {
                $dates[$dateAsIndex] = $dateIndexWithDate[$dateAsIndex]  ;
            }
        }
        return $dates ;
    }
    public function getMonthlyAmountAtMonthIndex(int $dateAsIndex)
    {
        return $this->getMonthlyAmounts()[$dateAsIndex] ?? 0 ;
    }
    public function getFfeCountsAtDateIndex(int $dateIndex)
    {
        return $this->getFfeCounts()[$dateIndex]??0;
    }
    public function getTotalItemCostAtDateIndex(int $monthIndex):float
    {
        $counts = $this->getCounts();
        $count = $counts[$monthIndex] ?? 0 ;
        $fixedAssetAmount = $this->getItemCostAtDateIndex($monthIndex);
        $contingencyRate = $this->getContingencyRate() / 100;
        $totalFixedAssetAmount = $count* $fixedAssetAmount ;
        return (1+$contingencyRate) * $totalFixedAssetAmount ;
    }
    public function getFfeCounts():array
    {
        return $this->getCounts();
    }
    public function getReplacementCostRate():float
    {
        return $this->replacement_cost_rate ;
    }
    public function getCostAnnualIncreaseRate():float 
    {
        return $this->cost_annual_increase_rate ?: 0;
    }
    public function getCollectionPolicyValue():array
    {
        if ($this->getPaymentTerm() == 'cash') {
            return [
                0 => 100
            ];
        }
        return $this->custom_collection_policy;
    }
    public function getPaymentRate(int $rateIndex)
    {
        return array_values($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
    }
    public function getPaymentRateAtDueInDays($rateIndex)
    {
        return array_keys($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
    }
    public function getPositionIds():array
    {
        return $this->position_ids?:[];
    }public function getDepartmentIds():array 
    {
        return $this->department_ids?:[];
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * * new
     */
    
    
    public function calculateFFEAssetsForFFE(int $fixedAssetEndDateAsIndex, int  $transferredDateForFFEAsIndex, float  $transferredAmount, array $studyDates, int $studyEndDateAsIndex, Study $study):array
    {
        
        $depreciationDurationInMonthsForFFE = $this->getDepreciationDurationInMonths();
        $ffeReplacementCostRateForFFE = $this->getReplacementCostRate()  ;
        $ffeReplacementIntervalInMonthsForFFE = $this->getReplacementIntervalInMonths();
        $projectUnderProgressForFFE = [
            'transferred_date_and_vales'=>[
                $transferredDateForFFEAsIndex =>  $transferredAmount
            ]
        ];
        return  $this->calculateFFEAssets($fixedAssetEndDateAsIndex, $depreciationDurationInMonthsForFFE, $ffeReplacementCostRateForFFE, $ffeReplacementIntervalInMonthsForFFE, $projectUnderProgressForFFE, $studyDates, $studyEndDateAsIndex, $study);
    }
    
    public function calculateFFEAssets(int $fixedAssetEndDateAsIndex, int $propertyDepreciationDurationInMonths, float $propertyReplacementCostRate, int $propertyReplacementIntervalInMonths, array $projectUnderProgressForConstruction, array $studyDates, int $studyEndDateAsIndex, Study $study):array
    {
        $buildingAssets = [];
     //   $datesAsStringAndIndex = $study->getDatesAsStringAndIndex();
        $operationStartDateAsIndex = $study->getOperationStartDateAsIndex();
        // $fixedAssetEndDateAsIndex = $this->getEndDateAsIndex();
        $operationStartDateAsIndex  =  $operationStartDateAsIndex >= $fixedAssetEndDateAsIndex ? $operationStartDateAsIndex :$fixedAssetEndDateAsIndex;
        $propertyReplacementCostRate = $propertyReplacementCostRate /100;
        $constructionTransferredDateAndValue = $projectUnderProgressForConstruction['transferred_date_and_vales']??[];
        $constructionTransferredDateAsIndex = array_key_last($constructionTransferredDateAndValue);
        $constructionTransferredValue = $constructionTransferredDateAndValue[$constructionTransferredDateAsIndex]??0;
        
        

        $beginningBalance = 0;
        $totalMonthlyDepreciation = [];
        $accumulatedDepreciation = [];
        $replacementDates = HArr::calculateReplacementDates($studyDates, $operationStartDateAsIndex, $studyEndDateAsIndex, $propertyReplacementIntervalInMonths);
        $depreciation = [];
        $index = 0 ;
        $depreciationStartDateAsIndex = null;
        foreach ($studyDates as $dateAsIndex) {
            if ($constructionTransferredDateAsIndex < $operationStartDateAsIndex) {
                $depreciationStartDateAsIndex = $operationStartDateAsIndex;
            } else {
                $depreciationStartDateAsIndex = $dateAsIndex+1;
            }
            $depreciationEndDateAsIndex = $depreciationStartDateAsIndex >=0  ?  $depreciationStartDateAsIndex+ $propertyDepreciationDurationInMonths - 1 : null;
    
            $buildingAssets['beginning_balance'][$dateAsIndex]= $beginningBalance;
            $buildingAssets['additions'][$dateAsIndex]=  $dateAsIndex ==$constructionTransferredDateAsIndex ? $constructionTransferredValue : 0;
            $buildingAssets['initial_total_gross'][$dateAsIndex] =  $buildingAssets['additions'][$dateAsIndex] +  $beginningBalance;
            $currentInitialTotalGross = $buildingAssets['initial_total_gross'][$dateAsIndex] ??0;
            $replacementCost[$dateAsIndex] =    in_array($dateAsIndex, $replacementDates)  ? $this->calculateReplacementCost($currentInitialTotalGross, $propertyReplacementCostRate) : 0;
            if (in_array($dateAsIndex, $replacementDates) && ($constructionTransferredDateAsIndex <= $operationStartDateAsIndex)) {
                $depreciationEndDateAsIndex = $dateAsIndex+1+$propertyDepreciationDurationInMonths-1;
            }
            $replacementValueAtCurrentDate = $replacementCost[$dateAsIndex] ?? 0;
            $buildingAssets['replacement_cost'][$dateAsIndex] = $replacementCost[$dateAsIndex] ;
            $buildingAssets['final_total_gross'][$dateAsIndex] = $buildingAssets['initial_total_gross'][$dateAsIndex]  + $replacementValueAtCurrentDate;
            $depreciation[$dateAsIndex]=$this->calculateMonthlyDepreciation($dateAsIndex, $buildingAssets['additions'][$dateAsIndex], $replacementValueAtCurrentDate, $propertyDepreciationDurationInMonths, $depreciationStartDateAsIndex, $depreciationEndDateAsIndex, $totalMonthlyDepreciation, $accumulatedDepreciation, $studyDates);
            $accumulatedDepreciation = HArr::calculateAccumulatedDepreciation($totalMonthlyDepreciation, $studyDates);
            $buildingAssets['total_monthly_depreciation'] =$totalMonthlyDepreciation;
            $buildingAssets['accumulated_depreciation'] =$accumulatedDepreciation;
            $currentAccumulatedDepreciation = $buildingAssets['accumulated_depreciation'][$dateAsIndex] ?? 0;
            $buildingAssets['end_balance'][$dateAsIndex] =  $buildingAssets['final_total_gross'][$dateAsIndex] -  $currentAccumulatedDepreciation;
            $beginningBalance = $buildingAssets['final_total_gross'][$dateAsIndex];
            $index++;
        }
        return $buildingAssets ;
    }
    
    

    protected function calculateReplacementCost(float $totalGross, float $propertyReplacementCostRate)
    {
        return $totalGross * $propertyReplacementCostRate ;
    }
    
    protected function calculateMonthlyDepreciation(int $replacementDate, float $additions, float $replacementCost, int $propertyDepreciationDurationInMonths, ?int $depreciationStartDateAsIndex, ?int $depreciationEndDateAsIndex, &$totalMonthlyDepreciation, &$accumulatedDepreciation, array $studyDates)
    {
        if (is_null($depreciationStartDateAsIndex) || is_null($depreciationEndDateAsIndex)) {
            return [];
        }
        $monthlyDepreciations = [];
        $monthlyDepreciationAtCurrentDate =  $propertyDepreciationDurationInMonths ? ($additions+$replacementCost) / $propertyDepreciationDurationInMonths  : 0;
        $depreciationDates = generateDatesBetweenTwoIndexedDates($depreciationStartDateAsIndex, $depreciationEndDateAsIndex);

        foreach ($studyDates as $dateAsIndex) {
            if ($dateAsIndex <= $replacementDate) {
                continue;
            }
            $previousDateAsIndex = $dateAsIndex-1;
            if (in_array($dateAsIndex, $depreciationDates)) {
                $monthlyDepreciations[$dateAsIndex] = $monthlyDepreciationAtCurrentDate;
                $totalMonthlyDepreciation[$dateAsIndex] = isset($totalMonthlyDepreciation[$dateAsIndex]) ? $totalMonthlyDepreciation[$dateAsIndex] +$monthlyDepreciationAtCurrentDate : $monthlyDepreciationAtCurrentDate;
                $currentAccumulatedDepreciation = $accumulatedDepreciation[$previousDateAsIndex]??0;
                $accumulatedDepreciation[$dateAsIndex] = $previousDateAsIndex >=0 ? ($totalMonthlyDepreciation[$dateAsIndex] + $currentAccumulatedDepreciation) : $totalMonthlyDepreciation[$dateAsIndex];
            } else {
                $accumulatedDepreciation[$dateAsIndex] = $accumulatedDepreciation[$previousDateAsIndex] ?? 0 ;
            }
        }
        return $monthlyDepreciations;
    }
    /**
     * * for old data only
     */
    public function getItemCost():float
    {
        return $this->ffe_item_cost?:0;
    }
    public function getItemCostAtDateIndex(int $dateAsIndex)
    {
        $itemCost = $this->getItemCost();
        $vatRate = $this->getVatRate();
        $study = $this->study;
        $studyStartDateAsString = $study->getStudyStartDate();
        $dateWithDateIndex = $study->getDateWithDateIndex();
        $studyEndDateAsIndex = $study->getStudyEndDateAsIndex();
        $studyStartDateAsIndex = $study->getStudyStartDateAsIndex($dateWithDateIndex, $studyStartDateAsString);
        $increaseRate = $this->getCostAnnualIncreaseRate();
        $withholdRate = $this->getWithholdTaxRate();
        $isDeductible = false ;
        $result = (new MonthlyFixedRepeatingAmountEquation())->calculate($itemCost, $studyStartDateAsIndex, $studyEndDateAsIndex, 'annually', $increaseRate, $isDeductible, $vatRate, $withholdRate);
        return $result['total_after_vat'][$dateAsIndex]??0;
        
    
        
        
    }
    // public function getTotalItemsCost($fixedAssets):float
    // {
    // 	$total = 0;
    // 	$fixedAssets->each(function($ffeItem) use (&$total){
    // 		$total += $ffeItem->getItemCost() * (1+($ffeItem->getContingencyRate()/100));
    // 	});
    // 	return $total ;
    // 	// return $this->getCounts() * $this->getAmount();
    // }
    public function getAmount()
    {
        return $this->amount ?: 0 ;
    }
    public function getDuration()
    {
        return 0;
    }


    
    public function getStartDateAsIndex()
    {
        return $this->start_date;
    
    }
    public function getEndDateAsIndex()
    {
        return $this->end_date;
    }
    /**
     * return [DateAsIndex => count ]
     */
    public function getCounts():array
    {
        $studyDates = $this->study->getCalculatedExtendedStudyDates();
        if ($this->isGeneral()) {
            return (array)$this->ffe_counts;
        }
        if ($this->isPerEmployee()) {
            $positions = $this->position_ids?:[] ;
            $result = [];
            foreach ($positions as $positionId) {
                $manpowers = Manpower::where('study_id', $this->study->id)->where('position_id', $positionId)->get();
				foreach($manpowers as $manpower){
					$currentHiringCounts = $manpower->hiring_counts;
					$result = HArr::sumAtDates([$result,$currentHiringCounts], $studyDates);
				}
                
            }
            return $result ;
        }
		return [];
        // if ($this->isPerNewBranch()) {
		// 	$studyDates = $this->study->getDateWithDateIndex();
		// 	$result = [];
		// 	foreach($this->study->newBranchMicrofinanceOpeningProjections as $newBranchOpeningProjections){
		// 		$currentCount = $newBranchOpeningProjections->counts;
		// 		$startDate = $newBranchOpeningProjections->start_date;
		// 		$currentItems = [$startDate => $currentCount ];
		// 		$currentItems = HArr::fillMissedKeysByZero($currentItems,$studyDates);
		// 		foreach($currentItems as $dateAsIndex => $count){
		// 			$result[$dateAsIndex] = isset($result[$dateAsIndex]) ? $result[$dateAsIndex] + $count : $count;
		// 		}
		// 	}
		// 	return $result;
        // }
    }
    public function getFfeEquityPayment()
    {
        return $this->ffe_equity_payment?:[];
    }
		public function getDueDays():array 
	{
		$collections = (array)$this->custom_collection_policy;
		$result = [];
		foreach($collections as $dueDay => $rate){
			$result[] = (int)$dueDay;
		}
		return $result;
	}
	public function getRates():array 
	{
		$collections = (array)$this->custom_collection_policy;
		$result = [];
		foreach($collections as $dueDay => $rate){
			$result[] = $rate;
		}
		return $result;
	}
	public static function generateFFERow(?self $fixedAsset,array $dates)
	{
		$ffeCounts= [];
			foreach($dates as $dateIndex=>$dateFormatted){
				$ffeCounts[$dateIndex] = $fixedAsset && isset($fixedAsset->ffe_counts[$dateIndex]) ? $fixedAsset->ffe_counts[$dateIndex] : 0;
			}
			$ffeCounts = (object)$ffeCounts;
		$fixedAssetType = self::FFE;
		return [
				'id'=>$fixedAsset ? $fixedAsset->id : 0,
				'type'=>$fixedAssetType,
				'name_id'=>$fixedAsset ? $fixedAsset->getNameId() : 0,
				'ffe_item_cost'=>$fixedAsset ? $fixedAsset->getItemCost() : 0,
				'contingency_rate'=>$fixedAsset ? $fixedAsset->getContingencyRate() : 0,
				'cost_annual_increase_rate'=>$fixedAsset ? $fixedAsset->getCostAnnualIncreaseRate() : 0,
				 'payment_terms'=>$fixedAsset ? $fixedAsset->payment_terms : 'cash',
                    'due_days'=>$fixedAsset ? $fixedAsset->getDueDays() : [],
                    'payment_rate'=>$fixedAsset ? $fixedAsset->getRates() : [],
					'depreciation_duration'=>$fixedAsset ? $fixedAsset->getDepreciationDuration() : 2,
					'replacement_cost_rate'=>$fixedAsset ? $fixedAsset->getReplacementCostRate() : 0,
					'replacement_interval'=>$fixedAsset ? $fixedAsset->getReplacementInterval() : 1,
					'ffe_counts'=>$ffeCounts,
			];
	}
	// public static function generateNewBranchRow(?self $fixedAsset)
	// {
		
	// 	$fixedAssetType = self::NEW_BRANCH;
	// 	return [
	// 			'id'=>$fixedAsset ? $fixedAsset->id : 0,
	// 			'type'=>$fixedAssetType,
	// 			'name_id'=>$fixedAsset ? $fixedAsset->getNameId() : 0,
	// 			'ffe_item_cost'=>$fixedAsset ? $fixedAsset->getItemCost() : 0,
	// 			'contingency_rate'=>$fixedAsset ? $fixedAsset->getContingencyRate() : 0,
	// 			'cost_annual_increase_rate'=>$fixedAsset ? $fixedAsset->getCostAnnualIncreaseRate() : 0,
	// 			 'payment_terms'=>$fixedAsset ? $fixedAsset->payment_terms : 'cash',
	// 			 'counts'=>$fixedAsset ? $fixedAsset->getCount() : 0,
    //                 'due_days'=>$fixedAsset ? $fixedAsset->getDueDays() : [],
    //                 'payment_rate'=>$fixedAsset ? $fixedAsset->getRates() : [],
	// 				'depreciation_duration'=>$fixedAsset ? $fixedAsset->getDepreciationDuration() : 2,
	// 				'replacement_cost_rate'=>$fixedAsset ? $fixedAsset->getReplacementCostRate() : 0,
	// 				'replacement_interval'=>$fixedAsset ? $fixedAsset->getReplacementInterval() : 1,
				
	// 		];
	// }
	public static function generatePerEmployeeRow(?self $fixedAsset,array $positionPerDepartments)
	{
		$fixedAssetType = self::PER_EMPLOYEE;
		
		return [
				'id'=>$fixedAsset ? $fixedAsset->id : 0,
				'type'=>$fixedAssetType,
				'name_id'=>$fixedAsset ? $fixedAsset->name_id : 0,
				'department_ids'=> $departmentIds = $fixedAsset && $fixedAsset->department_ids ? $fixedAsset->getDepartmentIds() : [],
				'position_ids'=> $fixedAsset && $fixedAsset->position_ids ? convertArrayNumericValuesToStringValues($fixedAsset->getPositionIds()) : [],
				'filteredPositionsOptions'=>$fixedAsset  ?  getOnlyFilterOptions($positionPerDepartments , $departmentIds ) : [],
				'ffe_item_cost'=>$fixedAsset  ? $fixedAsset->getItemCost() : 0,
				'contingency_rate'=>$fixedAsset ? $fixedAsset->getContingencyRate() : 0,
				'cost_annual_increase_rate'=>$fixedAsset ? $fixedAsset->getCostAnnualIncreaseRate() : 0,
				 'payment_terms'=>$fixedAsset ? $fixedAsset->payment_terms : 'cash',
                    'due_days'=>$fixedAsset ? $fixedAsset->getDueDays() : [],
                    'payment_rate'=>$fixedAsset ? $fixedAsset->getRates() : [],
					'depreciation_duration'=>$fixedAsset ? $fixedAsset->getDepreciationDuration() : 2,
					'replacement_cost_rate'=>$fixedAsset ? $fixedAsset->getReplacementCostRate() : 0,
					'replacement_interval'=>$fixedAsset ? $fixedAsset->getReplacementInterval() : 1,
					'counts'=>$fixedAsset ? $fixedAsset->getCount() : 0,
			];
	}
}
