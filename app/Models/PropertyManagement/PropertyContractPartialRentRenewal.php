<?php

namespace App\Models\PropertyManagement;

use App\Equations\MonthlyFixedRepeatingAmountEquation;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use App\ReadyFunctions\CollectionPolicyService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPropertyContractPartialRentRenewal
 */
class PropertyContractPartialRentRenewal extends Model
{
	
	use BelongsToStudy,BelongsToCompany;
	protected $table ='property_contract_partial_rent_renewals';
	protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
 	protected $guarded = ['id'];
	protected $casts = [
		'rent_revenues'=>'array',
		'rent_collections'=>'array',
	];
	public function property():BelongsTo
	{
		return $this->belongsTo(Property::class,'property_id','id');
	}	
	public function study():BelongsTo
	{
		return $this->belongsTo(Study::class,'study_id','id');
	}
	public function contract():BelongsTo
	{
		return $this->belongsTo(Contract::class,'contract_id','id');
	}
	public function getRenovateDuration():int
	{
		return $this->renovate_duration?:0;
	}
	public function calculateContractRenewal()
	{
	//	$property = $this->property;
		$study = $this->study;
		$contract = $this->contract;
		// $rentRevenueAtEndDate = 1000;
		$rentRevenueAtEndDate = $this->getNewRentAmount() > 0 ? $this->getNewRentAmount() : $contract->getRentRevenueAtContractEndDate();
		$renewalIncreaseRate = $this->getRenewalIncreaseRate()/100;
		$renewalRentAmount = $rentRevenueAtEndDate * (1+$renewalIncreaseRate);
		$startDateAsIndex = $contract->getEndDateAsIndex($study)+1+$this->getRenovateDuration();
		$renewalDuration = $this->getRenewalDuration();
		$endDateAsIndex = $startDateAsIndex + $renewalDuration ;
		$renewalAnnualIncreaseRate = $this->getRenewalAnnualIncreaseRate();
		$vatRate = 0;
		$withholdRate = 0;
		
		$result = (new MonthlyFixedRepeatingAmountEquation())->calculate($renewalRentAmount, $startDateAsIndex, $endDateAsIndex, 'annually', $renewalAnnualIncreaseRate, false, $vatRate, $withholdRate,[],null,1);
		$renewalRentRevenues = $result['total_before_vat']??[];
		$renewalRentCollection = $result['total_after_vat']??[];
		$renewalRentCollections = (new CollectionPolicyService())->applyCollectionPolicy(true,'system_default', $this->getCollectionInterval(), $renewalRentCollection);

		
		
		$this->update([
			'rent_revenues'=>$renewalRentRevenues,
			'rent_collections'=>$renewalRentCollections
		]);
		
	}
	public function getCollectionInterval():string
	{
		return $this->collection_interval?:'monthly';
	}
	public function getRenewalIncreaseRate():float
	{
		return $this->renewal_increase_rate?:0;
	}
	public function getRenewalDuration():int
	{
		return $this->renewal_duration?:0;
	}
	public function getRenewalAnnualIncreaseRate():float
	{
		return $this->renewal_annual_increase?:0;
	}
	public function getNewRentAmount():float
	{
		return $this->new_rent_amount?:0;
	}
	public static function getPartialRentCoveragesAmounts(Study $study ):array
	{
		$propertyContractPartialRentRenewals = self::with(['property','contract'])->where('study_id', $study->id)->get();
		foreach(['rent_revenues','rent_collections'] as $columnName){
			$formattedResult = [];
			$incomeStatementColumnName = $columnName == 'rent_revenues' ? 'partial_coverage_rent_revenues' : 'partial_coverage_rent_collections';
			$currentStatementReportClass = $columnName == 'rent_revenues' ? IncomeStatementReport::class : CashflowStatementReport::class;
        foreach ($propertyContractPartialRentRenewals as $propertyContractPartialRentRenewal) {
			$revenueOrCollectionAmounts = $propertyContractPartialRentRenewal->{$columnName}?:[];
			foreach($revenueOrCollectionAmounts as $dateAsIndex=> $val) {
				$formattedResult[$dateAsIndex] = isset($formattedResult[$dateAsIndex]) ? $formattedResult[$dateAsIndex] + $val : $val;
			}
			$contract = $propertyContractPartialRentRenewal->contract;
            if ($contract) {
                $rowResult = $contract->{$columnName}?:[];
                foreach ($rowResult as $dateAsString => $val) {
                    $dateAsString = Carbon::make($dateAsString)->format('Y-m-01');
                    $dateAsIndex = $study->getDateIndexFromString($dateAsString);
                    if (is_null($dateAsIndex)) {
                        continue;
                    }
                    $formattedResult[$dateAsIndex] = isset($formattedResult[$dateAsIndex]) ? $formattedResult[$dateAsIndex] + $val : $val;
                }
            }
        }
		if($currentStatementReportClass == CashflowStatementReport::class){
			$study->storeInCashFlowStatementReport( [$incomeStatementColumnName=> json_encode($formattedResult)]);
		}else{
			$study->storeInIncomeStatementReport( [$incomeStatementColumnName=> json_encode($formattedResult)]);
		}
	}
		
		return $formattedResult;
	}
}
