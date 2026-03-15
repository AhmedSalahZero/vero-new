<?php
namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $fixed_asset_type
 * @property int $is_fully_funded_though_equity
 * @property array<array-key, mixed>|null $direct_ffe_amounts
 * @property array<array-key, mixed>|null $equity_funding_rates
 * @property array<array-key, mixed>|null $equity_funding_values
 * @property array<array-key, mixed>|null $new_loans_funding_rates
 * @property array<array-key, mixed>|null $new_loans_funding_values
 * @property array<array-key, mixed>|null $tenors
 * @property array<array-key, mixed>|null $grace_periods
 * @property array<array-key, mixed>|null $interest_rates
 * @property array<array-key, mixed>|null $installment_intervals
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereDirectFfeAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereEquityFundingRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereEquityFundingValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereFixedAssetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereGracePeriods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereInstallmentIntervals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereInterestRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereIsFullyFundedThoughEquity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereNewLoansFundingRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereNewLoansFundingValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereTenors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetsFundingStructure whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FixedAssetsFundingStructure extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'property_management';
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
	// public function getDirectFfeAmountsAtMonthIndex(int $monthIndex):float
	// {
	// 	return $this->getFfeAmounts()[$monthIndex] ?? 0  ; 
	// }
	// public function getFfeAmounts():array 
	// {
	// 	return (array)$this->direct_ffe_amounts;
	// }
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
	// public function getPricing()
	// {
	// 	return $this->getMarginRate();
	// }
	
}
