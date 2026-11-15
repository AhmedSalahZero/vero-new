<?php
namespace App\Models\NonBankingService;

use App\Helpers\HArr;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
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
 * @property numeric $financial_regulatory_authority_rate
 * @property numeric $max_financial_regulatory_authority_rate
 * @property array<array-key, mixed> $employee_profit_share_rates
 * @property array<array-key, mixed> $border_of_directors_profit_share_rates
 * @property array<array-key, mixed> $shareholders_first_dividend_portions
 * @property array<array-key, mixed> $shareholders_dividend_payout_ratios
 * @property array<array-key, mixed> $shareholders_dividend_in_cash_or_shares
 * @property array<array-key, mixed>|null $salaries_annual_increase_rates (DC2Type:json)
 * @property array<array-key, mixed> $cbe_lending_corridor_rates
 * @property array<array-key, mixed>|null $cbe_base_lending_corridor_rates
 * @property array<array-key, mixed>|null $cbe_corridor_changes_rates
 * @property array<array-key, mixed> $bank_lending_margin_rates
 * @property array<array-key, mixed>|null $odas_bank_lending_margin_rates
 * @property array<array-key, mixed> $credit_interest_rate_for_surplus_cash
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $from_dispersement_of
 * @property array<array-key, mixed>|null $from_dispersement_of_rates
 * @property int $to_cover_cost
 * @property array<array-key, mixed>|null $to_cover_cost_rates
 * @property array<array-key, mixed>|null $min_cash_balances
 * @property-read \App\Models\NonBankingService\Study $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereBankLendingMarginRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereBorderOfDirectorsProfitShareRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereCbeBaseLendingCorridorRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereCbeCorridorChangesRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereCbeLendingCorridorRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereCreditInterestRateForSurplusCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereEmployeeProfitShareRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereFinancialRegulatoryAuthorityRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereFromDispersementOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereFromDispersementOfRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereLegalReserveRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereMaxFinancialRegulatoryAuthorityRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereMaxLegalReserveRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereMinCashBalances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereOdasBankLendingMarginRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereSalariesAnnualIncreaseRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereShareholdersDividendInCashOrShares($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereShareholdersDividendPayoutRatios($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereShareholdersFirstDividendPortions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereToCoverCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereToCoverCostRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\GeneralAndReserveAssumption whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralAndReserveAssumption extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy,HasCollectionOrPaymentStatement;
	protected $connection= 'non_banking_service';
	const LEASING_BANK_LENDING_MARGIN_RATE = 'leasing';
	const FACTORING_BANK_LENDING_MARGIN_RATE = 'factoring';
	const MORTGAGE_BANK_LENDING_MARGIN_RATE = 'mortgage';
	const MICROFINANCE_BANK_LENDING_MARGIN_RATE = 'microfinance';
	const CONSUMER_BANK_LENDING_MARGIN_RATE = 'consumer';
	public static function getBankLendingMarginTypes():array
	{
		return [
			self::LEASING_BANK_LENDING_MARGIN_RATE,
			self::FACTORING_BANK_LENDING_MARGIN_RATE,
			self::MORTGAGE_BANK_LENDING_MARGIN_RATE,
			self::MICROFINANCE_BANK_LENDING_MARGIN_RATE,
			self::CONSUMER_BANK_LENDING_MARGIN_RATE,
		];
	}
	/** * سلاسل بالشهر (index 0 = أول شهر في الدراسة) اللي صفحة General Assumptions بتعدلها */
	public const MONTH_SERIES_COLUMNS = [
		'cbe_lending_corridor_rates', 'cbe_base_lending_corridor_rates', 'cbe_corridor_changes_rates',
		'odas_bank_lending_margin_rates', 'credit_interest_rate_for_surplus_cash',
		'from_dispersement_of_rates', 'to_cover_cost_rates', 'min_cash_balances',
	];
	/** * سلاسل بالسنة (index 0 = أول سنة في الدراسة) اللي الصفحة بتعدلها */
	public const YEAR_SERIES_COLUMNS = [
		'employee_profit_share_rates', 'border_of_directors_profit_share_rates', 'shareholders_first_dividend_portions',
		'shareholders_dividend_payout_ratios', 'shareholders_dividend_in_cash_or_shares',
	];
	/** * قيم مفردة اللي الصفحة بتعدلها */
	public const SCALAR_COLUMNS = [
		'legal_reserve_rate', 'max_legal_reserve_rate', 'financial_regulatory_authority_rate',
		'max_financial_regulatory_authority_rate', 'from_dispersement_of', 'to_cover_cost',
	];

	/**
	 * * بيدمج اللي الصفحة بعتته على القيم المحفوظة ، شهر بشهر وسنة بسنة ، بدل ما يستبدلها.
	 * * الصفحة بتعرض شهور التشغيل بس ، فلو الحفظ استبدل العمود كله ، الشهور اللي مش ظاهرة بتتمسح
	 * * (مثال: بداية التشغيل اتأخرت من يناير ليوليو واتحفظت ، ورجعت يناير: يناير–يونيو بقوا أصفار).
	 * * بيرجّع الأعمدة اللي اتبعتت بس ، والأعمدة المسموحة بس.
	 */
	public static function mergeEditedValues(?self $saved, array $posted): array
	{
		$data = [];
		foreach (self::SCALAR_COLUMNS as $column) {
			if (array_key_exists($column, $posted)) {
				$data[$column] = $posted[$column];
			}
		}
		foreach (array_merge(self::MONTH_SERIES_COLUMNS, self::YEAR_SERIES_COLUMNS) as $column) {
			if (array_key_exists($column, $posted)) {
				$data[$column] = array_replace((array) ($saved ? $saved->{$column} : []), (array) ($posted[$column] ?? []));
			}
		}
		if (array_key_exists('bank_lending_margin_rates', $posted)) {
			$old = self::normalizeBankLendingMarginRates($saved ? $saved->bank_lending_margin_rates : []);
			$new = self::normalizeBankLendingMarginRates($posted['bank_lending_margin_rates']);
			foreach (self::getBankLendingMarginTypes() as $type) {
				$data['bank_lending_margin_rates'][$type] = array_replace($old[$type], $new[$type]);
			}
		}

		return $data;
	}

	/**
	 * * الأشكال القديمة اللي الصفحة الحالية مش بتقراها (بتبان أصفار ، وأي حفظ بعدها بيمسحها):
	 * * - هامش البنك قايمة واحدة لكل التمويل ← نفس القيم لكل نوع.
	 * * - الكوريدور الصافي متسجل والأساس فاضي ← الأساس = الصافي − التغيير.
	 * * بيرجّع الأعمدة اللي محتاجة تتعدل بس (بقيمها الجديدة) ، فاضي لو الصف بالشكل الحالي.
	 *
	 * @param  array<string, mixed>  $columns  decoded JSON columns of one row
	 * @return array<string, array>
	 */
	public static function upgradeLegacyShapes(array $columns): array
	{
		$changes = [];
		$margins = is_array($columns['bank_lending_margin_rates'] ?? null) ? $columns['bank_lending_margin_rates'] : [];
		if ($margins !== [] && array_intersect(array_keys($margins), self::getBankLendingMarginTypes()) === []) {
			$changes['bank_lending_margin_rates'] = self::normalizeBankLendingMarginRates($margins);
		}
		$base = is_array($columns['cbe_base_lending_corridor_rates'] ?? null) ? $columns['cbe_base_lending_corridor_rates'] : [];
		$net = is_array($columns['cbe_lending_corridor_rates'] ?? null) ? $columns['cbe_lending_corridor_rates'] : [];
		if ($base === [] && $net !== []) {
			$corridorChanges = is_array($columns['cbe_corridor_changes_rates'] ?? null) ? $columns['cbe_corridor_changes_rates'] : [];
			foreach ($net as $index => $rate) {
				$changes['cbe_base_lending_corridor_rates'][$index] = (float) $rate - (float) ($corridorChanges[$index] ?? 0);
			}
		}

		return $changes;
	}

	/**
	 * * لما تاريخ بداية الدراسة يتحرك ، أسعار السوق (الكوريدور ، الهوامش ، فايدة الكاش ، الحد الأدنى للكاش)
	 * * بتفضل على نفس شهور التقويم: كل شهر بيتنقل $shiftMonths خانة (موجب لو البداية بقت أبكر).
	 * * الشهور اللي بقت قبل البداية الجديدة بتتشال ، والشهور الأولى الجديدة بتاخد أول قيمة معروفة.
	 * * مدخلات الخطة اللي بالسنة (نسب التوزيع) بتفضل بترتيبها.
	 *
	 * @return array<string, array> the shifted month series, ready for update()
	 */
	public function shiftedToNewStudyStart(int $shiftMonths): array
	{
		$shift = function (array $series) use ($shiftMonths): array {
			$out = [];
			foreach ($series as $key => $value) {
				if (is_numeric($key) && (int) $key + $shiftMonths >= 0) {
					$out[(int) $key + $shiftMonths] = $value;
				}
			}
			ksort($out);
			if ($out !== []) {
				$first = array_key_first($out);
				for ($i = 0; $i < $first; $i++) {
					$out[$i] = $out[$first];
				}
				ksort($out);
			}

			return $out;
		};
		$data = [];
		foreach (self::MONTH_SERIES_COLUMNS as $column) {
			$data[$column] = $shift((array) $this->{$column});
		}
		foreach (self::normalizeBankLendingMarginRates($this->bank_lending_margin_rates) as $type => $series) {
			$data['bank_lending_margin_rates'][$type] = $shift($series);
		}

		return $data;
	}

	/**
	 * * هامش البنك في الشكل القديم كان قايمة واحدة لكل أنواع التمويل ({monthIndex: rate}) ،
	 * * والشكل الحالي لكل نوع ({type: {monthIndex: rate}}). الشكل القديم بيتنسخ لكل الأنواع.
	 */
	public static function normalizeBankLendingMarginRates(mixed $raw): array
	{
		$raw = is_array($raw) ? $raw : [];
		$types = self::getBankLendingMarginTypes();
		$isLegacy = $raw !== [] && array_intersect(array_keys($raw), $types) === [];
		$out = [];
		foreach ($types as $type) {
			$out[$type] = $isLegacy ? $raw : (array) ($raw[$type] ?? []);
		}

		return $out;
	}

	/**
	 * * قيمة الشهر ، ولو الشهر مش متسجل: قيمة آخر شهر قبله متسجل (السعر بيفضل لحد ما يتغير).
	 * * مثال: المدة زادت من 3 لـ 5 سنين ولسه الصفحة ماتحفظتش: شهور السنة الرابعة والخامسة بتاخد سعر آخر شهر.
	 */
	public static function valueAtOrBefore(array $series, int $index, float $default = 0): float
	{
		if (isset($series[$index]) && is_numeric($series[$index])) {
			return (float) $series[$index];
		}
		$best = null;
		foreach ($series as $key => $value) {
			if (is_numeric($key) && (int) $key < $index && is_numeric($value) && ($best === null || (int) $key > $best)) {
				$best = (int) $key;
			}
		}

		return $best === null ? $default : (float) $series[$best];
	}

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
		'cbe_corridor_changes_rates'=>'array',
		'cbe_base_lending_corridor_rates'=>'array',
		'bank_lending_margin_rates'=>'array',
		'odas_bank_lending_margin_rates'=>'array',
		'credit_interest_rate_for_surplus_cash'=>'array',
		'from_dispersement_of_rates'=>'array',
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
				}if($generalAndReserveAssumption->isDirty('cbe_corridor_changes_rates') ){
					$cashAndBank = $study->getCashAndBank();
					$cashAndBank ? $cashAndBank->recalculateEquityOpeningBalanceStatements() : null ;
					/** * فوايد قروض الرصيد الافتتاحي بتتغير مع الكوريدور كمان: بتتحسب في saving بتاع الموديل */
					foreach ($study->supplierPayableOpeningBalances as $supplierPayableOpeningBalance) {
						$supplierPayableOpeningBalance->save();
					}
				}
				if($generalAndReserveAssumption->isDirty('salaries_annual_increase_rates')){
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
	public function getShareholderDividendInCashOrSharesAtYear(int $yearIndex)
	{
		return $this->shareholders_dividend_in_cash_or_shares[$yearIndex] ?? 0  ; 
	}
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
	
	public function getCbeBaseLendingCorridorRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->cbe_base_lending_corridor_rates[$yearOrMonthIndex] ?? 0  ; 
	}
	
	public function getCbeBaseLendingCorridorRates():array 
	{
		return $this->cbe_base_lending_corridor_rates?:[] ;
	}
	
	public function getCbeCorridorChangesRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->cbe_corridor_changes_rates[$yearOrMonthIndex] ?? 0  ; 
	}
	
	public function getCbeCorridorChangesRates():array 
	{
		return $this->cbe_corridor_changes_rates?:[] ;
	}
	
	public function getBaseRatesPerMonths(string $type)
	{
		$study = $this->study;
		/**
		 * @var Study $study
		 */
		$operationDurationPerYear = $study->getOperationDurationPerYearFromIndexes();
		$baseRates = $this->getCbeLendingCorridorRates() ;
		$baseRatesPerMonths =[];
		$dateIndexWithDate = $study->getDateIndexWithDate() ;
		
		foreach ($operationDurationPerYear as $yearIndex => $yearMonthIndexes) {
            foreach ($yearMonthIndexes as $monthIndex => $monthlyZeroOrOne) {
      //          $yearOrMonthIndex = $study->isMonthlyStudy() ? $monthIndex : $yearIndex;
                $baseRatesPerMonths[Carbon::make($dateIndexWithDate[$monthIndex])->format('Y-m-d')] = self::valueAtOrBefore($baseRates, (int) $monthIndex);
            }
        }
		$baseRatesMapping =   $baseRatesPerMonths  ;
		// $baseRatesMapping =  $study->isMonthlyStudy() ? $baseRatesPerMonths  : HArr::getFirstOfYear($baseRatesPerMonths);
        $bankLendingMarginRates=$this->getBankLendingMarginRates($type);
        $baseRatesMapping = HArr::isAllValuesEqual($baseRatesMapping, $bankLendingMarginRates);
		
		return $baseRatesMapping;
		
	}
	public function getBankLendingMarginRates(string $type):array 
	{
		return self::normalizeBankLendingMarginRates($this->bank_lending_margin_rates)[$type] ?? [];
	}
	public function getBankLendingMarginRatesAtYearOrMonthIndex(int $yearOrMonthIndex,string $type)
	{
		
		return $this->getBankLendingMarginRates($type)[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getOdasBankLendingMarginRates():array 
	{
		return (array) $this->odas_bank_lending_margin_rates ; 
	}
	public function getOdasBankLendingMarginRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getOdasBankLendingMarginRates()[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getFromDispersementOf():int
	{
		return $this->from_dispersement_of ?: 1;
	}public function getToCoverCostOf():int
	{
		return $this->to_cover_cost ?: 1;
	}
	public function getFromDispersementOfRates():array 
	{
		return $this->from_dispersement_of_rates?:[]; 
	}
	public function getFromDispersementOfRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getFromDispersementOfRates()[$yearOrMonthIndex] ?? 0  ; 
	}
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
	public function getFinancialRegulatoryAuthorityRate()
	{
		return $this->financial_regulatory_authority_rate ?: 0;
	}
	public function getFinancialRegulatoryAuthorityRateFormatted():string 
	{
		return number_format($this->getFinancialRegulatoryAuthorityRate(),2);
	}
	public function getMaxFinancialRegulatoryAuthorityRate()
	{
		return $this->max_financial_regulatory_authority_rate ?: 0;
	}
	public function getMaxFinancialRegulatoryAuthorityRateFormatted():string 
	{
		return number_format($this->getMaxFinancialRegulatoryAuthorityRate(),2);
	}

		
}
