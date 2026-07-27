<?php

namespace App\Models\PropertyManagement;

use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCompany;
use App\Traits\Models\HasPropertyDueInstallment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $property_id
 * @property string $installment_type
 * @property numeric|null $signing_payment
 * @property string|null $signing_payment_date
 * @property numeric|null $reservation_payment
 * @property string|null $reservation_payment_date
 * @property array<array-key, mixed>|null $regular_installments_amounts
 * @property string|null $annual_start_date
 * @property numeric|null $annual_amount
 * @property int|null $annual_count
 * @property string|null $delivery_payments_start_date
 * @property string|null $delivery_date
 * @property string|null $ready_to_use_date
 * @property numeric|null $delivery_payments_amount
 * @property int|null $delivery_payments_count
 * @property string|null $delivery_payments_payment_interval
 * @property string|null $maintenance_payments_start_date
 * @property numeric|null $maintenance_payments_amount
 * @property int|null $maintenance_payments_count
 * @property string|null $maintenance_payments_payment_interval
 * @property array<array-key, mixed>|null $variable_installment_amounts
 * @property array<array-key, mixed>|null $total_due_installments
 * @property int $company_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int $has_annually_installments
 * @property int $has_delivery_payments
 * @property int $has_maintenance_payments
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Property|null $property
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereAnnualAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereAnnualCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereAnnualStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereDeliveryPaymentsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereDeliveryPaymentsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereDeliveryPaymentsPaymentInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereDeliveryPaymentsStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereHasAnnuallyInstallments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereHasDeliveryPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereHasMaintenancePayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereInstallmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereMaintenancePaymentsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereMaintenancePaymentsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereMaintenancePaymentsPaymentInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereMaintenancePaymentsStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereReadyToUseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereRegularInstallmentsAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereReservationPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereReservationPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereSigningPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereSigningPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereTotalDueInstallments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyDueInstallment whereVariableInstallmentAmounts($value)
 * @mixin \Eloquent
 */
class PropertyDueInstallment extends Model
{
    use HasFactory, HasBasicStoreRequest,HasCompany,HasPropertyDueInstallment;
    protected $connection = PROPERTY_MANAGEMENT_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
		'regular_installments_amounts'=>'array',
		'variable_installment_amounts'=>'array',
		'total_due_installments'=>'array',
    ];
	public function getDeliveryPaymentsStartDate():?string
    {
        return $this->delivery_payments_start_date;
    }
	
	public function getDeliveryPaymentsStartDateFormatted():?string
    {
        return $this->delivery_payments_start_date ? Carbon::make($this->getDeliveryPaymentsStartDate())->format('d-m-Y') : null;
    }
    public function getDeliveryPaymentsStartDateFormattedForVueDatePicker():array
    {
        return formatDateForVueDatePicker($this->getDeliveryPaymentsStartDate());
    }
	public function getMaintenancePaymentsStartDate():?string
    {
        return $this->maintenance_payments_start_date;
    }
    public function getMaintenancePaymentsStartDateFormatted():?string
    {
        return $this->maintenance_payments_start_date ? Carbon::make($this->getMaintenancePaymentsStartDate())->format('d-m-Y') : null;
    }
    public function getMaintenancePaymentsStartDateFormattedForVueDatePicker():array
    {
        return formatDateForVueDatePicker($this->getMaintenancePaymentsStartDate());
    }
	
	public function getDeliveryDate():?string
    {
        return $this->delivery_date;
    }
    public function getDeliveryDateFormatted():?string
    {
        return $this->delivery_date ? Carbon::make($this->getDeliveryDate())->format('d-m-Y') : null;
    }
    public function getDeliveryDateFormattedForVueDatePicker():array
    {
        return formatDateForVueDatePicker($this->getDeliveryDate());
    }
    public function setDeliveryDateAttribute($value)
    {
        $this->attributes['delivery_date'] = $value ? formatDateFromMonthPicker($value) : null;
    }
	
    public function getReadyToUseDate():?string
    {
        return $this->ready_to_use_date;
    }
    public function getReadyToUseDateFormatted():?string
    {
        return $this->ready_to_use_date ? Carbon::make($this->getReadyToUseDate())->format('d-m-Y') : null;
    }
    public function getReadyToUseDateFormattedForVueDatePicker():array
    {
        return formatDateForVueDatePicker($this->getReadyToUseDate());
    }

	
    public function setReadyToUseDateAttribute($value)
    {
        $this->attributes['ready_to_use_date'] = $value ? formatDateFromMonthPicker($value) : null;
    }
	public function getTotalDueInstallmentFormatted(array $studyDates):array
    {
        $result = [];
        foreach ($studyDates as $dateAsIndex=>$dateAsString) {
            $amount = $this->total_due_installments[$dateAsString] ?? 0;
            $result[$dateAsIndex] = $amount;
        }
        return $result;
    }
	public function getAnnualStartDate():?string
    {
        return $this->annual_start_date;
    }
    public function getAnnualStartDateFormatted():?string
    {
        return $this->annual_start_date ? Carbon::make($this->getAnnualStartDate())->format('d-m-Y') : null;
    }
    public function getAnnualStartDateFormattedForVueDatePicker():array
    {
        return formatDateForVueDatePicker($this->getAnnualStartDate());
    }
	public function getRegularInstallmentAmounts():array
    {
        if (count($this->regular_installments_amounts)) {
            $results = $this->regular_installments_amounts;
            foreach ($results as $index=>$regularInstallment) {
                $results[$index]['start_date'] = $regularInstallment['start_date'] ? formatDateForVueDatePicker($regularInstallment['start_date']) : formatDateForVueDatePicker(now()->format('Y-m-d'));
                $results[$index]['end_date'] = $regularInstallment['end_date'] ? formatDateForVueDatePicker($regularInstallment['end_date']) : formatDateForVueDatePicker(now()->addMonths(12)->format('Y-m-d'));
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
                $results[$index]['date'] = $regularInstallment['date'] ? formatDateForVueDatePicker($regularInstallment['date']) : formatDateForVueDatePicker(now()->format('Y-m-d'));
            }
            return $results;
        }
        return self::getDefaultVariableInstallmentAmounts();
    }
	
	
    public function getSigningPaymentDate():?string
    {
        return $this->signing_payment_date;
    }
	public function getSigningPaymentDateAsString():string
	{
		return $this->getSigningPaymentDate() ? Carbon::make($this->getSigningPaymentDate())->format('Y-m-d') : null;
	}
	
    public function getSigningPaymentDateFormatted():?string
    {
        return $this->signing_payment_date ? Carbon::make($this->getSigningPaymentDate())->format('d-m-Y') : null;
    }
	public function getSigningPaymentDateFormattedForVueDatePicker():array
	{
		return formatDateForVueDatePicker($this->getSigningPaymentDate());
	}
 
   
    public function getReservationPaymentDate():?string
    {
        return $this->reservation_payment_date;
    }
	public function getReservationPaymentDateAsString():?string
	{
		return $this->getReservationPaymentDate() ? Carbon::make($this->getReservationPaymentDate())->format('Y-m-d') : null;
	}
    public function getReservationPaymentDateFormatted():?string
    {
        return $this->reservation_payment_date ? Carbon::make($this->getReservationPaymentDate())->format('d-m-Y') : null;
    }
    public function getReservationPaymentFormattedForVueDatePicker():array
    {
        return formatDateForVueDatePicker($this->getReservationPaymentDate());
    }
	
}
