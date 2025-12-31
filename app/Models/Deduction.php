<?php

namespace App\Models;

use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCreatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
	use HasBasicStoreRequest,HasCreatedAt;
	const DEDUCTIONS = 'deductions';
	
	protected $guarded = ['id'];
	public function getId()
	{
		return $this->id;
	}
	public function getName()
	{
		return $this->name;
	}
	
	public function scopeOnlyForCompany(Builder $builder , int $companyId)
	{
		return $builder->where('company_id',$companyId);
	}
	
	public static function calculateAmountInMainCurrency($amount , string $date , string $invoiceCurrency , $invoiceExchangeRate  ,Company $company):array 
	{
		$mainFunctionCurrency = $company->getMainFunctionalCurrency();
		$date = Carbon::make($date)->format('Y-m-d');
		if($invoiceCurrency == $mainFunctionCurrency){
			return [
				'amount_in_main_currency'=>$amount,
				'amount_in_invoice_exchange_rate'=>$amount
			];
		}
		$foreignExchangeRate  = ForeignExchangeRate::getExchangeRateForCurrencyAndClosestDate($invoiceCurrency,$mainFunctionCurrency,$date,$company->id );
		return [
			'amount_in_main_currency'=>$amount * $foreignExchangeRate,
			'amount_in_invoice_exchange_rate'=> $amount * $invoiceExchangeRate
		] ;
	}

}
