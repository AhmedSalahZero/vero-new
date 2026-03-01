<?php

namespace App\Models\PropertyManagement;

use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCompany;
use App\Traits\Models\HasPropertyDueInstallment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperForecastedPropertyDueInstallment
 */
class ForecastedPropertyDueInstallment extends Model
{
    use HasFactory, HasBasicStoreRequest,HasCompany,HasPropertyDueInstallment;
    protected $connection = PROPERTY_MANAGEMENT_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
		'regular_installments_amounts'=>'array',
		'variable_installment_amounts'=>'array',
		'total_due_installments'=>'array',
		
    ];
	public function study()
	{
		return $this->belongsTo(Study::class,'study_id','id');
	}
	
	public function setDeliveryDateAttribute(array $deliveryDate)
	{
		$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		$this->attributes['delivery_date'] = $study->getIndexDateFromString(convertJsDateToDB($deliveryDate['year'],$deliveryDate['month'])) ;
	}
	
	public function getDeliveryDateAsIndex():int 
	{
		return $this->delivery_date?:0;
	}
	public function getDeliveryDateAsString():string 
	{
		return $this->study->getDateFromDateIndex($this->getDeliveryDateAsIndex());
	}
	public function getDeliveryDateFormattedForVueDatePicker():array
	{
		$deliveryDateAsIndex = $this->getDeliveryDateAsIndex();
		return $this->delivery_date ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($deliveryDateAsIndex)) : formatDateForVueDatePicker(date('Y-m-d'));
	}
	
	
	
	
	
	public function setReadyToUseDateAttribute(array $readyToUseDate)
	{
		$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		$this->attributes['ready_to_use_date'] = $study->getIndexDateFromString(convertJsDateToDB($readyToUseDate['year'],$readyToUseDate['month'])) ;
	}
	
	public function getReadyToUseDateAsIndex():int 
	{
		return $this->ready_to_use_date?:0;
	}
	public function getReadyToUseDateAsString():string 
	{
		return $this->study->getDateFromDateIndex($this->getReadyToUseDateAsIndex());
	}
	public function getReadyToUseDateFormattedForVueDatePicker():array
	{
		$readyToUseDateAsIndex = $this->getReadyToUseDateAsIndex();
		return $this->ready_to_use_date ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($readyToUseDateAsIndex)) : formatDateForVueDatePicker(date('Y-m-d'));
	}
	
	public function setSigningPaymentDateAttribute(array $signingPaymentDate)
	{
		$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		$this->attributes['signing_payment_date'] = $study->getIndexDateFromString(convertJsDateToDB($signingPaymentDate['year'],$signingPaymentDate['month'])) ;
	}
	
	public function getSigningPaymentDateAsIndex():int 
	{
		return $this->signing_payment_date?:0;
	}
	public function getSigningPaymentDateAsString():string 
	{
		return $this->study->getDateFromDateIndex($this->getSigningPaymentDateAsIndex());
	}
	public function getSigningPaymentDateFormattedForVueDatePicker():array
	{
		$signingPaymentDateAsIndex = $this->getSigningPaymentDateAsIndex();
		return $this->signing_payment_date ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($signingPaymentDateAsIndex)) : formatDateForVueDatePicker(date('Y-m-d'));
	}
	
	
	public function setReservationPaymentDateAttribute(array $reservationPaymentDate)
	{
		$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		$this->attributes['reservation_payment_date'] = $study->getIndexDateFromString(convertJsDateToDB($reservationPaymentDate['year'],$reservationPaymentDate['month'])) ;
	}
	
	public function getReservationPaymentDateAsIndex():int 
	{
		return $this->reservation_payment_date?:0;
	}
	public function getReservationPaymentDateAsString():string 
	{	
		return $this->study->getDateFromDateIndex($this->getReservationPaymentDateAsIndex());
	}
	public function getReservationPaymentDateFormattedForVueDatePicker():array
	{
		$reservationPaymentDateAsIndex = $this->getReservationPaymentDateAsIndex();
		return $this->reservation_payment_date ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($reservationPaymentDateAsIndex)) : formatDateForVueDatePicker(date('Y-m-d'));
	}
	
	
	public function setAnnualStartDateAttribute(array $annualStartDate)
	{
		$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		$this->attributes['annual_start_date'] = $study->getIndexDateFromString(convertJsDateToDB($annualStartDate['year'],$annualStartDate['month'])) ;
	}
	public function getAnnualStartDateAsIndex():int 
	{
		return $this->annual_start_date?:0;
	}
	public function getAnnualStartDateAsString():string 
	{	
		return $this->study->getDateFromDateIndex($this->getAnnualStartDateAsIndex());
	}
	public function getAnnualStartDateFormattedForVueDatePicker():array
	{
		$annualStartDateAsIndex = $this->getAnnualStartDateAsIndex();
		return $this->annual_start_date ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($annualStartDateAsIndex)) : formatDateForVueDatePicker(date('Y-m-d'));
	}
	
	public function setDeliveryPaymentsStartDateAttribute(array $deliveryPaymentsStartDate)
	{
		$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		$this->attributes['delivery_payments_start_date'] = $study->getIndexDateFromString(convertJsDateToDB($deliveryPaymentsStartDate['year'],$deliveryPaymentsStartDate['month'])) ;
	}
	
	public function getDeliveryPaymentsStartDateAsIndex():int 
	{
		return $this->delivery_payments_start_date?:0;
	}
	public function getDeliveryPaymentsStartDateAsString():string 
	{	
		return $this->study->getDateFromDateIndex($this->getDeliveryPaymentsStartDateAsIndex());
	}
	public function getDeliveryPaymentsStartDateFormattedForVueDatePicker():array
	{
		$deliveryPaymentsStartDateAsIndex = $this->getDeliveryPaymentsStartDateAsIndex();
		return $this->delivery_payments_start_date ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($deliveryPaymentsStartDateAsIndex)) : formatDateForVueDatePicker(date('Y-m-d'));
	}
	
	public function setMaintenancePaymentsStartDateAttribute(array $maintenancePaymentsStartDate)
	{
		$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		$this->attributes['maintenance_payments_start_date'] = $study->getIndexDateFromString(convertJsDateToDB($maintenancePaymentsStartDate['year'],$maintenancePaymentsStartDate['month'])) ;
	}
	
	public function getMaintenancePaymentsStartDateAsIndex():int 
	{
		return $this->maintenance_payments_start_date?:0;
	}
	public function getMaintenancePaymentsStartDateAsString():string 
	{	
		return $this->study->getDateFromDateIndex($this->getMaintenancePaymentsStartDateAsIndex());
	}
	public function getMaintenancePaymentsStartDateFormattedForVueDatePicker():array
	{
		$maintenancePaymentsStartDateAsIndex = $this->getMaintenancePaymentsStartDateAsIndex();
		return $this->maintenance_payments_start_date ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($maintenancePaymentsStartDateAsIndex)) : formatDateForVueDatePicker(date('Y-m-d'));
	}
	
	public function setRegularInstallmentsAmountsAttribute(array $regularInstallmentAmounts)
	{
		$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		foreach($regularInstallmentAmounts as &$regularInstallmentAmount){
			$regularInstallmentAmount['start_date'] = $study->getIndexDateFromString(convertJsDateToDB($regularInstallmentAmount['start_date']['year'],$regularInstallmentAmount['start_date']['month'])) ;
			$regularInstallmentAmount['end_date'] = $study->getIndexDateFromString(convertJsDateToDB($regularInstallmentAmount['end_date']['year'],$regularInstallmentAmount['end_date']['month'])) ;
		}
		$this->attributes['regular_installments_amounts'] = json_encode($regularInstallmentAmounts);
	}
	public function setVariableInstallmentAmountsAttribute(array $variableInstallmentAmounts)
	{
			$studyId = Request()->segment(5);
		$study = Study::find($studyId);
		foreach($variableInstallmentAmounts as &$variableInstallmentAmount){
			$variableInstallmentAmount['date'] = $study->getIndexDateFromString(convertJsDateToDB($variableInstallmentAmount['date']['year'],$variableInstallmentAmount['date']['month'])) ;
		}
		$this->attributes['variable_installment_amounts'] = json_encode($variableInstallmentAmounts);
	}
	// public function getDeliveryPaymentsStartDate():?string
    // {
    //     return $this->getDeliveryPaymentsStartDateAsString();
    // }
	public function getReservationPaymentFormattedForVueDatePicker():array
    {
        return formatDateForVueDatePicker($this->getReservationPaymentDateAsString());
    }
	
	public function getRegularInstallmentAmounts():array
    {
        if (count($this->regular_installments_amounts)) {
            $results = $this->regular_installments_amounts;
            foreach ($results as $index=>$regularInstallment) {
                $results[$index]['start_date'] = $regularInstallment['start_date'] ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($regularInstallment['start_date'])) : formatDateForVueDatePicker(now()->format('Y-m-d'));
                $results[$index]['end_date'] = $regularInstallment['end_date'] ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($regularInstallment['end_date'])) : formatDateForVueDatePicker(now()->addMonths(12)->format('Y-m-d'));
            }
            return $results;
        }
        return self::getDefaultRegularInstallmentAmounts();
    }
    public function getVariableInstallmentAmounts():array
    {
        if (count($this->variable_installment_amounts)) {
            $results = $this->variable_installment_amounts;
            foreach ($results as $index=>$regularInstallment) {
                $results[$index]['date'] = $regularInstallment['date'] ? formatDateForVueDatePicker($this->study->getDateFromDateIndex($regularInstallment['date'])) : formatDateForVueDatePicker(now()->format('Y-m-d'));
            }
            return $results;
        }
        return self::getDefaultVariableInstallmentAmounts();
    }
	
}
