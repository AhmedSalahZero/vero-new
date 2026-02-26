<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InvoiceDeduction extends Model
{
	protected $guarded = [
		'id'
	];
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}
	public function customerInvoice()
	{
		return $this->belongsTo(CustomerInvoice::class,'invoice_id','id');
	}
	public function supplierInvoice()
	{
		return $this->belongsTo(SupplierInvoice::class,'invoice_id','id');
	}
	public function getInvoice()
	{
		if($this->invoice_type == 'CustomerInvoice'){

			return $this->customerInvoice;
		}
		if($this->invoice_type == 'SupplierInvoice'){
			return $this->supplierInvoice;
		}
		throw new \Exception('custom exception .. invalid invoice_type');
		
	}
	public function getDate()
    {
        return $this->date ;
    }
	public function getDateFormatted()
    {
		$date = $this->getDate() ;
        return $date ? Carbon::make($date)->format('d-m-Y') : null   ;
    }
	public function setDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		
		$this->attributes['date'] = $year.'-'.$month.'-'.$day;
	}
	public function getDueDateFormattedForDatePicker()
	{
		$date = $this->getDate();
		return $date ? Carbon::make($date)->format('m/d/Y') : null;
	}
	public function getAmount()
	{
		return $this->amount ;
	}
	public function getAmountFormatted()
	{
		$amount = $this->getAmount();
		return number_format($amount) ;
	}
	public static function getForInvoices(array $invoiceIds , string $modelType ,string $startDate , string $endDate ){
		return self::whereIn('invoice_id',$invoiceIds)->where('invoice_type',$modelType)->whereBetween('date',[$startDate,$endDate])->get();
	}
	public function deduction()
	{
		return $this->belongsTo(Deduction::class,'deduction_id');
	}
	public function getDeductionName()
	{
		return $this->deduction ? $this->deduction->getName() : __('N/A');
	}
}
