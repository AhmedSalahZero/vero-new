<?php
namespace App\Traits\Models;

use App\Models\PropertyManagement\Property;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPropertyDueInstallment
{
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
    public function getInstallmentType():string
    {
        return $this->installment_type;
    }
    public function isVariableInstallment():bool
    {
        return $this->installment_type === 'variable';
    }
    public function isRegularInstallment():bool
    {
        return $this->installment_type === 'regular';
    }
  
    public static function getDefaultRegularInstallmentAmounts():array
    {
        return [
            [
                'amount'=>0,
            'installment_count'=>0,
            'start_date'=>formatDateForVueDatePicker(now()->format('Y-m-d')),
            'end_date'=>formatDateForVueDatePicker(now()->addMonths(12)->format('Y-m-d')),
            'installment_payment_interval'=>'monthly',
            ]
        ];
    }
   
    public static function getDefaultVariableInstallmentAmounts():array
    {
        return [
            [
                'date'=>formatDateForVueDatePicker(now()->format('Y-m-d')),
                'amount'=>0,
            ]
        ];
    }
    
	public function getSigningPayment():float
    {
        return $this->signing_payment?:0;
    }
    
    
    public function getAnnualAmount():float
    {
        return $this->annual_amount?:0;
    }
    public function getAnnualAmountFormatted():?string
    {
        return $this->annual_amount ? number_format($this->getAnnualAmount()) : null;
    }
    public function getAnnualCount():int
    {
        return $this->annual_count?:0;
    }
    public function getAnnualCountFormatted():?string
    {
        return $this->annual_count ? number_format($this->getAnnualCount()) : null;
    }
    public function getDeliveryPaymentsAmount():float
    {
        return $this->delivery_payments_amount?:0;
    }
    public function getDeliveryPaymentsAmountFormatted():?string
    {
        return $this->delivery_payments_amount ? number_format($this->getDeliveryPaymentsAmount()) : null;
    }
    public function getDeliveryPaymentsPaymentInterval():?string
    {
        return $this->delivery_payments_payment_interval?:'monthly';
    }
    public function getMaintenancePaymentsAmount():float
    {
        return $this->maintenance_payments_amount?:0;
    }
    public function getMaintenancePaymentsAmountFormatted():?string
    {
        return $this->maintenance_payments_amount ? number_format($this->getMaintenancePaymentsAmount()) : null;
    }
    public function getMaintenancePaymentsPaymentInterval():?string
    {
        return $this->maintenance_payments_payment_interval?:'monthly';
    }
    public function getMaintenancePaymentsCount():int
    {
        return $this->maintenance_payments_count?:0;
    }
    public function getMaintenancePaymentsCountFormatted():?string
    {
        return $this->maintenance_payments_count ? number_format($this->getMaintenancePaymentsCount()) : null;
    }
    public function getDeliveryPaymentsCount():int
    {
        return $this->delivery_payments_count?:0;
    }
    public function getDeliveryPaymentsCountFormatted():?string
    {
        return $this->delivery_payments_count ? number_format($this->getDeliveryPaymentsCount()) : null;
    }
    public function getInstallmentPayments():array
    {
        return $this->installment_payments?:[];
    }
    public function getInstallmentPaymentsFormatted():array
    {
        return $this->installment_payments?:[];
    }
   
    
    public function getHasAnnuallyInstallments():bool
    {
        return (bool)$this->has_annually_installments?:0;
    }
    public function getHasDeliveryPayments():bool
    {
        return $this->has_delivery_payments?:0;
    }
    public function getHasMaintenancePayments():bool
    {
        return (bool)$this->has_maintenance_payments?:0;
    }
   
	public function getReservationPayment():float
    {
        return $this->reservation_payment?:0;
    }
	
}
