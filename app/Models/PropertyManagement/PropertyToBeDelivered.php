<?php

namespace App\Models\PropertyManagement;

use App\Equations\MonthlyFixedRepeatingAmountEquation;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use App\ReadyFunctions\CollectionPolicyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $study_id
 * @property int $property_id
 * @property int $company_id
 * @property int $renovate_duration
 * @property numeric $renovate_cost
 * @property numeric $monthly_rent_amount
 * @property string|null $collection_interval
 * @property int $rent_duration
 * @property numeric $rent_annual_increase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $rent_revenues
 * @property array<array-key, mixed>|null $rent_collections
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Property|null $property
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereCollectionInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereMonthlyRentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereRenovateCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereRenovateDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereRentAnnualIncrease($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereRentCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereRentDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereRentRevenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyToBeDelivered whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PropertyToBeDelivered extends Model
{
	
	use BelongsToStudy,BelongsToCompany;
	protected $table ='property_to_be_delivered';
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
	public function getMonthlyRentAmount():float 
	{
		return $this->monthly_rent_amount?:0;
	}
	public function getRentAnnualIncreaseRate():float
	{
		return $this->rent_annual_increase?:0;
	}
	public function getRentDuration():int
	{
		return $this->rent_duration?:0;
	}
	public function getRenovateDuration():int
	{
		return $this->renovate_duration?:0;
	}
	public function getCollectionInterval():string
	{
		return $this->collection_interval?:'monthly';
	}
	public function calculateToBeDeliveredRentRevenue()
	{
		
		$deliveryDate = $this->property->dueInstallment->getDeliveryDate();
		$study = $this->study;
		$deliveryDateAsIndex = $study->getIndexDateFromString($deliveryDate);
		$rentAmount = $this->getMonthlyRentAmount();
		$annualIncreaseRate = $this->getRentAnnualIncreaseRate();
		$startDateAsIndex = $deliveryDateAsIndex + $this->getRenovateDuration() + 1;
		$rentDuration = $this->getRentDuration();
		$endDateAsIndex = $startDateAsIndex + $rentDuration ;
		$vatRate = 0;
		$withholdRate = 0;
		
		$result = (new MonthlyFixedRepeatingAmountEquation())->calculate($rentAmount, $startDateAsIndex, $endDateAsIndex, 'annually', $annualIncreaseRate, false, $vatRate, $withholdRate,[],null,1);
		$renewalRentRevenues = $result['total_before_vat']??[];
		$renewalRentCollection = $result['total_after_vat']??[];
		$renewalRentCollections = (new CollectionPolicyService())->applyCollectionPolicy(true,'system_default', $this->getCollectionInterval(), $renewalRentCollection);
		$this->update([
			'rent_revenues'=>$renewalRentRevenues,
			'rent_collections'=>$renewalRentCollections
		]);
		
	}
	public static function getToBeDeliveredCoveragesAmounts(Study $study ):array
	{
		$propertyToBeDelivereds = self::with(['property'])->where('study_id', $study->id)->get();
		foreach(['rent_revenues','rent_collections'] as $columnName){
			$formattedResult = [];
			$incomeStatementColumnName = $columnName == 'rent_revenues' ? 'to_be_delivered_rent_revenues' : 'to_be_delivered_rent_collections';
			$currentStatementReportClass = $columnName == 'rent_revenues' ? IncomeStatementReport::class : CashflowStatementReport::class;
			foreach ($propertyToBeDelivereds as $propertyToBeDelivered) {
				$dateAndValues = $propertyToBeDelivered->{$columnName}?:[];
				foreach ($dateAndValues as $dateAsIndex => $val) {
					$formattedResult[$dateAsIndex] = isset($formattedResult[$dateAsIndex]) ? $formattedResult[$dateAsIndex] + $val : $val;
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
