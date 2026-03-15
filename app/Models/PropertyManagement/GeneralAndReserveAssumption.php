<?php
namespace App\Models\PropertyManagement;

use App\Helpers\HArr;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCollectionOrPaymentStatement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @property int $id
 * @property int $study_id
 * @property numeric $legal_reserve_rate
 * @property numeric $max_legal_reserve_rate
 * @property array<array-key, mixed> $employee_profit_share_rates
 * @property array<array-key, mixed> $border_of_directors_profit_share_rates
 * @property array<array-key, mixed> $shareholders_first_dividend_portions
 * @property array<array-key, mixed> $shareholders_dividend_payout_ratios
 * @property array<array-key, mixed>|null $salaries_annual_increase_rates (DC2Type:json)
 * @property array<array-key, mixed> $cbe_lending_corridor_rates
 * @property array<array-key, mixed> $bank_lending_margin_rates
 * @property array<array-key, mixed>|null $odas_bank_lending_margin_rates
 * @property array<array-key, mixed> $credit_interest_rate_for_surplus_cash
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $to_cover_cost
 * @property array<array-key, mixed>|null $to_cover_cost_rates
 * @property array<array-key, mixed>|null $min_cash_balances
 * @property-read \App\Models\PropertyManagement\Study $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereBankLendingMarginRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereBorderOfDirectorsProfitShareRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereCbeLendingCorridorRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereCreditInterestRateForSurplusCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereEmployeeProfitShareRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereLegalReserveRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereMaxLegalReserveRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereMinCashBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereOdasBankLendingMarginRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereSalariesAnnualIncreaseRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereShareholdersDividendPayoutRatios($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereShareholdersFirstDividendPortions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereToCoverCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereToCoverCostRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\GeneralAndReserveAssumption whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralAndReserveAssumption extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy,HasCollectionOrPaymentStatement;
	protected $connection= 'property_management';
	// const LEASING_BANK_LENDING_MARGIN_RATE = 'leasing';
	// const FACTORING_BANK_LENDING_MARGIN_RATE = 'factoring';
	// const MORTGAGE_BANK_LENDING_MARGIN_RATE = 'mortgage';
	// const MICROFINANCE_BANK_LENDING_MARGIN_RATE = 'microfinance';
	// const CONSUMER_BANK_LENDING_MARGIN_RATE = 'consumer';
	// public static function getBankLendingMarginTypes():array
	// {
	// 	return [
	// 		self::LEASING_BANK_LENDING_MARGIN_RATE,
	// 		self::FACTORING_BANK_LENDING_MARGIN_RATE,
	// 		self::MORTGAGE_BANK_LENDING_MARGIN_RATE,
	// 		self::MICROFINANCE_BANK_LENDING_MARGIN_RATE,
	// 		self::CONSUMER_BANK_LENDING_MARGIN_RATE,
	// 	];
	// }
	protected $guarded = ['id'];
	protected $casts = [
		'employee_profit_share_rates'=>'array',
		'border_of_directors_profit_share_rates'=>'array',
		'shareholders_first_dividend_portions'=>'array',
		'shareholders_dividend_payout_ratios'=>'array',
		'shareholders_dividend_in_cash_or_shares'=>'array',
		'salaries_annual_increase_rates'=>'array',
		// 'expense_annual_increase_rates'=>'array',
		'cbe_lending_corridor_rates'=>'array',
		// 'cbe_corridor_changes_rates'=>'array',
		// 'cbe_base_lending_corridor_rates'=>'array',
		'bank_lending_margin_rates'=>'array',
		'odas_bank_lending_margin_rates'=>'array',
		'credit_interest_rate_for_surplus_cash'=>'array',
		// 'from_dispersement_of_rates'=>'array',
		'to_cover_cost_rates'=>'array',
		'min_cash_balances'=>'array',
		'dividend_statement'=>'array',
		];
		
		
		public static function boot()
		{
			parent::boot();
			static::updated(function(self $generalAndReserveAssumption){
				$study = $generalAndReserveAssumption->study ;
				/**
				 * @var Study $study 
				 */
				if($generalAndReserveAssumption->isDirty('cbe_lending_corridor_rates') || $generalAndReserveAssumption->isDirty('bank_lending_margin_rates')){
					$study->recalculateAllRevenuesLoans(new Request);
				}
				if($study->isDirty('salaries_annual_increase_rates')){
					$study->recalculateManpower();
				 } 
			});
		}
		
	public function getEmployeeProfitShareRatesAtYearIndex(int $yearIndex)
	{
		return $this->employee_profit_share_rates[$yearIndex] ?? 0  ; 
	}
	public function getBorderOfDirectorsProfitShareRateAtYearIndex(int $yearIndex)
	{
		return $this->border_of_directors_profit_share_rates[$yearIndex] ?? 0  ; 
	}
	public function getShareholderFirstDividendPortionAtYearIndex(int $yearIndex)
	{
		return $this->shareholders_first_dividend_portions[$yearIndex] ?? 0  ; 
	}
	public function getShareholderDividendPayoutRatioAtYearIndex(int $yearIndex)
	{
		return $this->shareholders_dividend_payout_ratios[$yearIndex] ?? 0  ; 
	}
	// public function getShareholderDividendInCashOrSharesAtYear(int $yearIndex)
	// {
	// 	return $this->shareholders_dividend_in_cash_or_shares[$yearIndex] ?? 0  ; 
	// }
	public function getSalariesAnnualIncreaseRateAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->salaries_annual_increase_rates[$yearOrMonthIndex] ?? 0  ; 
	}
	// public function getExpenseAnnualIncreaseRateAtYearOrMonthIndex(int $yearOrMonthIndex)
	// {
	// 	return $this->expense_annual_increase_rates[$yearOrMonthIndex] ?? 0  ; 
	// }
	
	public function getCbeLendingCorridorRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->cbe_lending_corridor_rates[$yearOrMonthIndex] ?? 0  ; 
	}
	
	public function getCbeLendingCorridorRates():array 
	{
		return $this->cbe_lending_corridor_rates?:[] ;
	}
	
	// public function getCbeBaseLendingCorridorRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	// {
	// 	return $this->cbe_base_lending_corridor_rates[$yearOrMonthIndex] ?? 0  ; 
	// }
	
	// public function getCbeBaseLendingCorridorRates():array 
	// {
	// 	return $this->cbe_base_lending_corridor_rates?:[] ;
	// }
	
	// public function getCbeCorridorChangesRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	// {
	// 	return $this->cbe_corridor_changes_rates[$yearOrMonthIndex] ?? 0  ; 
	// }
	
	// public function getCbeCorridorChangesRates():array 
	// {
	// 	return $this->cbe_corridor_changes_rates?:[] ;
	// }
	
	public function getBaseRatesPerMonths()
	{
		$study = $this->study;
		/**
		 * @var Study $study
		 */
		$operationDurationPerYear = $study->getOperationDurationPerYearFromIndexes();
		$baseRates = $this->getCbeLendingCorridorRates() ;
		$baseRatesPerMonths =[];
		$dateIndexWithDate = $study->getDateIndexWithDate() ;
		/**
		 * @var array $dateIndexWithDate
		 */
		foreach ($operationDurationPerYear as $yearIndex => $yearMonthIndexes) {
            foreach ($yearMonthIndexes as $monthIndex => $monthlyZeroOrOne) {
      //          $yearOrMonthIndex = $study->isMonthlyStudy() ? $monthIndex : $yearIndex;
                $baseRatesPerMonths[Carbon::make($dateIndexWithDate[$monthIndex])->format('Y-m-d')] = $baseRates[$monthIndex];
            }
        }
		$baseRatesMapping =  $baseRatesPerMonths  ;
        $bankLendingMarginRates=$this->getBankLendingMarginRates();
        $baseRatesMapping = HArr::isAllValuesEqual($baseRatesMapping, $bankLendingMarginRates);
		
		return $baseRatesMapping;
		
	}
	public function getBankLendingMarginRates():array 
	{
		
		return (array) ($this->bank_lending_margin_rates??[]) ; 
	}
	public function getBankLendingMarginRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		
		return $this->getBankLendingMarginRates()[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getOdasBankLendingMarginRates():array 
	{
		return (array) $this->odas_bank_lending_margin_rates ; 
	}
	public function getOdasBankLendingMarginRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getOdasBankLendingMarginRates()[$yearOrMonthIndex] ?? 0  ; 
	}
	// public function getFromDispersementOf():int
	// {
	// 	return $this->from_dispersement_of ?: 1;
	// }
	public function getToCoverCostOf():int
	{
		return $this->to_cover_cost ?: 1;
	}
	// public function getFromDispersementOfRates():array 
	// {
	// 	return $this->from_dispersement_of_rates?:[]; 
	// }
	// public function getFromDispersementOfRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	// {
	// 	return $this->getFromDispersementOfRates()[$yearOrMonthIndex] ?? 0  ; 
	// }
	public function getToCostCostRates():array 
	{
		return $this->to_cover_cost_rates?:[]; 
	}
	public function getToCoverCostRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getToCostCostRates()[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getMinCashBalances():array 
	{
		return $this->min_cash_balances?:[]; 
	}
	public function getMinCashBalancesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getMinCashBalances()[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getCreditInterestRateForSurplusCash():array 
	{
		return $this->credit_interest_rate_for_surplus_cash?:[] ;
	}
	public function getCreditInterestRateForSurplusCashAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->credit_interest_rate_for_surplus_cash[$yearOrMonthIndex] ?? 0  ; 
	}
	
	
	public function getLegalReserveRate()
	{
		return $this->legal_reserve_rate ?: 0;
	}
	public function getLegalReserveRateFormatted():string 
	{
		return number_format($this->getLegalReserveRate(),2);
	}
	public function getMaxLegalReserveRate()
	{
		return $this->max_legal_reserve_rate ?: 0;
	}
	public function getMaxLegalReserveRateFormatted():string 
	{
		return number_format($this->getMaxLegalReserveRate(),2);
	}
	// public function getFinancialRegulatoryAuthorityRate()
	// {
	// 	return $this->financial_regulatory_authority_rate ?: 0;
	// }
	// public function getFinancialRegulatoryAuthorityRateFormatted():string 
	// {
	// 	return number_format($this->getFinancialRegulatoryAuthorityRate(),2);
	// }
	// public function getMaxFinancialRegulatoryAuthorityRate()
	// {
	// 	return $this->max_financial_regulatory_authority_rate ?: 0;
	// }
	// public function getMaxFinancialRegulatoryAuthorityRateFormatted():string 
	// {
	// 	return number_format($this->getMaxFinancialRegulatoryAuthorityRate(),2);
	// }

		
}
