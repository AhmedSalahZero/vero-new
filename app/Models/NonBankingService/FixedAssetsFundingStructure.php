<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  FixedAssetsFundingStructure extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $table='fixed_assets_funding_structures';
	protected $guarded = ['id'];
	protected $casts =[
		'direct_ffe_amounts'=>'array',
		'equity_funding_rates'=>'array',
		'equity_funding_values'=>'array',
		'new_loans_funding_rates'=>'array',
		'new_loans_funding_values'=>'array',
		'tenors'=>'array',
		'grace_periods'=>'array',
		'interest_rates'=>'array',
		'installment_intervals'=>'array',
	];
	
	public function getEquityFundingRatesAtMonthIndex(int $monthIndex):float
	{
		return $this->equity_funding_rates[$monthIndex] ?? 0  ; 
	}
	public function getDirectFfeAmountsAtMonthIndex(int $monthIndex):float
	{
		return $this->getFfeAmounts()[$monthIndex] ?? 0  ; 
	}
	public function getFfeAmounts():array 
	{
		return (array)$this->direct_ffe_amounts;
	}
	public function getTenorsAtMonthIndex(int $monthIndex):float
	{
		return $this->tenors[$monthIndex] ?? 0  ; 
	}
	public function getGracePeriodAtMonthIndex(int $monthIndex):float
	{
		return $this->grace_periods[$monthIndex] ?? 0  ; 
	}
	public function getInterestRateAtMonthIndex(int $monthIndex):float
	{
		return $this->getInterestRates()[$monthIndex] ?? 0  ; 
	}
	public function getInterestRates():array
	{
		return (array)$this->interest_rates;
	}
	
	public function getInstallmentIntervalAtMonthIndex(int $monthIndex):string
	{
		
		return $this->installment_intervals[$monthIndex] ?? 'monthly'  ; 
	}
	
	public function getEquityFundingValuesAtMonthIndex(int $monthIndex)
	{
		return $this->equity_funding_values[$monthIndex] ?? 0  ; 
	}

	public function getNewLoansFundingRatesAtMonthIndex(int $monthIndex)
	{
		return $this->new_loans_funding_rates[$monthIndex] ?? 0  ; 
	}
	public function getNewLoansFundingValuesAtMonthIndex(int $monthIndex)
	{
		return $this->new_loans_funding_values[$monthIndex] ?? 0  ; 
	}
	public function getLoanType():string
	{
		return 'grace_period_without_capitalization';
	}
	public function getBaseRate()
	{
		return 0 ; 
	}
	public function getMarginRateAtMonthIndex($dateAsIndex)
	{
		return $this->interest_rates[$dateAsIndex]??0;
	}
	public function getPricing()
	{
		return $this->getMarginRate();
	}
	
}
