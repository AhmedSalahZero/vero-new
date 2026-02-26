<?php

namespace App\Models\PropertyManagement;

use App\Equations\MonthlyFixedRepeatingAmountEquation;
use App\Helpers\HArr;
use App\Helpers\HDate;
use App\Models\Company;
use App\Models\PropertyManagement\PropertyDueInstallment;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use App\Models\Traits\Scopes\PropertyManagements\HasDueInstallment;
use App\ReadyFunctions\CollectionPolicyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ForecastedProperty extends Model
{
    use BelongsToStudy,BelongsToCompany,HasDueInstallment;
    protected $guarded = ['id'];
    protected $connection ='property_management';
    protected $casts = [
        'rent_revenues'=>'array',
        'rent_collections'=>'array',
    ];
    

    
    public function company():BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function getCategoryId():int
    {
        return $this->category_id?:0;
    }
    public function getTypeId():int
    {
        return $this->type_id?:0;
    }
	public function type():BelongsTo
	{
		return $this->belongsTo(PropertyType::class, 'type_id');
	}
    public function getCounts():int
    {
        return $this->counts?:0;
    }
    
    public function setAcquisitionDateAttribute(array $acquisitionDate)
    {
        $studyId = Request()->segment(5);
        $study = Study::find($studyId);
        $this->attributes['acquisition_date'] = $study->getIndexDateFromString(convertJsDateToDB($acquisitionDate['year'], $acquisitionDate['month'])) ;
    }
    
    public function getAcquisitionDateAsIndex():int
    {
        return $this->acquisition_date?:0;
    }
    public function getAcquisitionDateAsString():string
    {
        return $this->study->getDateFromDateIndex($this->getAcquisitionDateAsIndex());
    }
    public function getAcquisitionDateFormattedForVueDatePicker():array
    {
        $acquisitionDateAsIndex = $this->getAcquisitionDateAsIndex();
        return $this->acquisition_date ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($acquisitionDateAsIndex)) : formatDateForVueDatePicker(date('Y-m-d'));
    }
    public function getArea():float
    {
        return $this->area?:0;
    }
    public function getSqrPrice():float
    {
        return $this->sqr_price?:0;
    }
    public function getTotalAmount():float
    {
        return $this->getArea() * $this->getSqrPrice() * $this->getCounts();
    }
    public function getRenovateDuration():float
    {
        return $this->renovate_duration?:0;
    }
    public function getRenovateCost():float
    {
        return $this->renovate_cost?:0;
    }
    public function getMonthlyRentAmount():float
    {
        return $this->monthly_rent_amount?:0;
    }
    public function getCollectionInterval():string
    {
        return $this->collection_interval?:'monthly';
    }public function getRentDuration():string
    {
        return $this->rent_duration?:0;
    }
    public function getRentAnnualIncreaseRate():float
    {
        return $this->rent_annual_increase?:0;
    }
    public static function generateRow(?self $model, int $companyId, int $studyId):array
    {
        return [
            
            'id'=>$model ? $model->id : -1,
            'category_id'=>$model ?$model->getCategoryId():0,
            'type_id'=>$model  ? $model->getTypeId():0,
            'counts'=>$model ? $model->getCounts() :0,
            'acquisition_date'=>$model ? $model->getAcquisitionDateFormattedForVueDatePicker():formatDateForVueDatePicker(date('Y-m-d')),
            'area'=>$model ? $model->getArea():0,
            'sqr_price'=>$model ?$model->getSqrPrice():0,
            'total_amount'=>$model ? $model->getTotalAmount() : 0,
            'company_id'=>$companyId,
            'study_id'=>$studyId,
            'forecastedDueInstallment' => $model ? $model->getForecastedDueInstallmentsFormatted($studyId) : (new self)->getForecastedDueInstallmentsFormatted($studyId) ,
            'renovate_duration'=>$model ? $model->getRenovateDuration() : 0,
            'renovate_cost'=>$model ? $model->getRenovateCost() : 0,
            'monthly_rent_amount'=>$model ? $model->getMonthlyRentAmount() : 0,
            'collection_interval'=>$model ? $model->getCollectionInterval() : 'monthly',
            'rent_duration'=>$model ? $model->getRentDuration() : 0,
            'rent_annual_increase'=>$model ? $model->getRentAnnualIncreaseRate() : 0,
        ];
    }
    
 
    public function forecastedDueInstallment(): HasOne
    {
        return $this->hasOne(ForecastedPropertyDueInstallment::class, 'property_id');
    }
	public function dueInstallment(): HasOne
    {
        return $this->forecastedDueInstallment();
    }
   
    public function getForecastedDueInstallmentsFormatted(int $studyId):array
    {
        $forecastedDueInstallment = $this->forecastedDueInstallment;
        /**
         * @var PropertyDueInstallment $forecastedDueInstallment
         */
        return [
            'id' => $forecastedDueInstallment ? $forecastedDueInstallment->id : 0,
            'delivery_date'=>$forecastedDueInstallment ? $forecastedDueInstallment->getDeliveryDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
            'ready_to_use_date'=>$forecastedDueInstallment ? $forecastedDueInstallment->getReadyToUseDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
            'installment_type' => $forecastedDueInstallment ? $forecastedDueInstallment->getInstallmentType() : 'regular',
            'signing_payment' => $forecastedDueInstallment ? $forecastedDueInstallment->getSigningPayment() : 0,
            'signing_payment_date' =>$forecastedDueInstallment ? $forecastedDueInstallment->getSigningPaymentDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
            'reservation_payment' => $forecastedDueInstallment ? $forecastedDueInstallment->getReservationPayment() :0,
            'reservation_payment_date' => $forecastedDueInstallment ? $forecastedDueInstallment->getReservationPaymentFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
            'regular_installments_amounts' => $forecastedDueInstallment ? $forecastedDueInstallment->getRegularInstallmentAmounts() : PropertyDueInstallment::getDefaultRegularInstallmentAmounts(),
            'annual_start_date' => $forecastedDueInstallment ? $forecastedDueInstallment->getAnnualStartDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
            'annual_amount' => $forecastedDueInstallment ? $forecastedDueInstallment->getAnnualAmount() : 0,
            'annual_count' => $forecastedDueInstallment ? $forecastedDueInstallment->getAnnualCount() : 0,
            'delivery_payments_start_date' => $forecastedDueInstallment ? $forecastedDueInstallment->getDeliveryPaymentsStartDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
            'delivery_payments_amount' => $forecastedDueInstallment ? $forecastedDueInstallment->getDeliveryPaymentsAmount() : 0,
            'delivery_payments_payment_interval' => $forecastedDueInstallment ? $forecastedDueInstallment->getDeliveryPaymentsPaymentInterval() : 'monthly',
            'maintenance_payments_start_date' => $forecastedDueInstallment ? $forecastedDueInstallment->getMaintenancePaymentsStartDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
            'maintenance_payments_amount' => $forecastedDueInstallment ? $forecastedDueInstallment->getMaintenancePaymentsAmount() : 0,
            'maintenance_payments_count' => $forecastedDueInstallment ? $forecastedDueInstallment->getMaintenancePaymentsCount() : 0,
            'maintenance_payments_payment_interval' => $forecastedDueInstallment ? $forecastedDueInstallment->getMaintenancePaymentsPaymentInterval() : 'monthly',
            'delivery_payments_count' => $forecastedDueInstallment ? $forecastedDueInstallment->getDeliveryPaymentsCount() : 0,
            'installment_payments' => $forecastedDueInstallment ? $forecastedDueInstallment->getInstallmentPayments() : [],
            'variable_installment_amounts' => $forecastedDueInstallment ? $forecastedDueInstallment->getVariableInstallmentAmounts() : PropertyDueInstallment::getDefaultVariableInstallmentAmounts(),
            'has_annually_installments'=>$forecastedDueInstallment ? $forecastedDueInstallment->getHasAnnuallyInstallments() : 0,
            'has_delivery_payments'=>$forecastedDueInstallment ? $forecastedDueInstallment->getHasDeliveryPayments() : 0,
            'has_maintenance_payments'=>$forecastedDueInstallment ? $forecastedDueInstallment->getHasMaintenancePayments() : 0,
            'study_id'=>$studyId
        ];
    }
    public function calculateToBeDeliveredRentRevenue()
    {
    
     
        $deliveryDateAsIndex = $this->forecastedDueInstallment->getDeliveryDateAsIndex();
        $study = $this->study;
        $rentAmount = $this->getMonthlyRentAmount();
        $annualIncreaseRate = $this->getRentAnnualIncreaseRate();
        $startDateAsIndex = $deliveryDateAsIndex + $this->getRenovateDuration() + 1;
        $rentDuration = $this->getRentDuration();
        $endDateAsIndex = $startDateAsIndex + $rentDuration ;
        $vatRate = 0;
        $withholdRate = 0;
    
        $result = (new MonthlyFixedRepeatingAmountEquation())->calculate($rentAmount, $startDateAsIndex, $endDateAsIndex, 'annually', $annualIncreaseRate, false, $vatRate, $withholdRate, [], null, 1);
        $renewalRentRevenues = $result['total_before_vat']??[];
        $renewalRentCollection = $result['total_after_vat']??[];
        $renewalRentCollections = (new CollectionPolicyService())->applyCollectionPolicy(true, 'system_default', $this->getCollectionInterval(), $renewalRentCollection);
        $this->update([
            'rent_revenues'=>$renewalRentRevenues,
            'rent_collections'=>$renewalRentCollections
        ]);
		$study->storeInIncomeStatementReport( ['property_forecasted_rent_revenues'=> json_encode($renewalRentRevenues),'property_forecasted_rent_collections'=> json_encode($renewalRentCollections)]);
    }
   
    public static function getForecastedPropertiesCoveragesAmounts(Study $study):array
    {
        $forecastedProperties = self::where('study_id', $study->id)->get();
		foreach(['rent_revenues','rent_collections'] as $columnName){
			$formattedResult = [];
			$currentStatementReportClass = $columnName == 'rent_revenues' ? IncomeStatementReport::class : CashflowStatementReport::class;
			$incomeStatementOrCashFlowReportColumnName = $columnName == 'rent_revenues' ? 'property_forecasted_rent_revenues' : 'property_forecasted_rent_collections';
			foreach ($forecastedProperties as $forecastedProperty) {
				$dateAndValues = $forecastedProperty->{$columnName}?:[];
				foreach ($dateAndValues as $dateAsIndex => $val) {
					$formattedResult[$dateAsIndex] = isset($formattedResult[$dateAsIndex]) ? $formattedResult[$dateAsIndex] + $val : $val;
				}
			}
			if($currentStatementReportClass == CashflowStatementReport::class){
				$study->storeInCashFlowStatementReport( [$incomeStatementOrCashFlowReportColumnName=> json_encode($formattedResult)]);
			}else{
				$study->storeInIncomeStatementReport( [$incomeStatementOrCashFlowReportColumnName=> json_encode($formattedResult)]);
			}
		}
        return $formattedResult;
    }
	public static function recalculateDueInstallments(Study $study):void
	{
		$forecastedProperties = self::where('study_id', $study->id)->get();
		$totalDueInstallments = [];
		foreach($forecastedProperties as $forecastedProperty){
			$dueInstallment = $forecastedProperty->forecastedDueInstallment;
			if($dueInstallment){
				$currentTotalDueInstallments = $dueInstallment->total_due_installments?:[];
				$totalDueInstallments = HArr::sumAtDates([$totalDueInstallments,$currentTotalDueInstallments], array_keys($study->getStudyDates()));
			}
		}
		$study->cashflowStatementReport->update([
			'new_properties_installments'=>json_encode($totalDueInstallments)
		]);
		
	}
}
