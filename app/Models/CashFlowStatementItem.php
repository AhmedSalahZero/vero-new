<?php

namespace App\Models;

use App\Interfaces\Models\Interfaces\IFinancialStatementAbleItem;
use App\Models\Traits\Accessors\CashFlowStatementItemAccessor;
use App\Models\Traits\Relations\CashFlowStatementItemRelation;
use App\Models\Traits\Scopes\FinancialStatementAbleItemScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
/**
 * App\Models\CashFlowStatementItem
 *
 * @property int $id
 * @property string $name
 * @property bool $has_sub_items
 * @property bool $has_depreciation_or_amortization
 * @property bool $has_percentage_or_fixed_sub_items
 * @property string $financial_statement_able_type
 * @property bool $is_main_for_all_calculations
 * @property bool $is_sales_rate
 * @property bool $for_interval_comparing
 * @property string|null $depends_on
 * @property string|null $equation
 * @property bool $has_auto_depreciation
 * @property int $is_auto_depreciation_for
 * @property bool|null $is_accumulated
 * @property int|null $has_vat_rate
 * @property int|null $can_be_dedictiable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CashFlowStatement> $financialStatementAbles
 * @property-read int|null $financial_statement_ables_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CashFlowStatement> $subItems
 * @property-read int|null $sub_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CashFlowStatement> $mainRowsPivot
 * @property-read int|null $main_rows_pivot_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereHasSubItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereHasDepreciationOrAmortization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereHasPercentageOrFixedSubItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereFinancialStatementAbleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereIsMainForAllCalculations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereIsSalesRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereForIntervalComparing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereDependsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereEquation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereHasAutoDepreciation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereIsAutoDepreciationFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereIsAccumulated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereHasVatRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereCanBeDedictiable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatementItem whereUpdatedAt($value)
 * @property-read bool|null $financial_statement_ables_exists
 * @property-read bool|null $main_rows_pivot_exists
 * @property-read bool|null $sub_items_exists
 * @property-read \App\Models\IncomeStatementSubItem|null $pivot
 * @mixin \Eloquent
 */
class CashFlowStatementItem extends Model implements IFinancialStatementAbleItem
{
	protected $table = 'financial_statement_able_items';
	public static function percentageOfSalesRows(): array  // do not remove
	{
		return [];
	}

	use  CashFlowStatementItemRelation, CashFlowStatementItemAccessor;
	// must start from 1  not zero
	const CASH_IN_ID = 76;
	const CASH_OUT_ID = 77;
	const NET_CASH_PROFIT_ID = 78;
	const ACCUMULATED_NET_CASH = 79;
	protected $guarded = [
		'id'
	];

	public static function formattedViewForDashboard(): array
	{
		return CashFlowStatementItem::where('for_interval_comparing', 1)->pluck('name', 'id')->toArray();
	}

	public static function compareBetweenTowItems(Collection $firstItems, array $firstIntervalOfDates, string $firstCashFlowStatementDurationType, Collection $secondItems, array $secondIntervalOfDates, string $secondCashFlowStatementDurationType, ?string $firstMainItemName, ?string $secondMainItemName): array
	{


		$firstItems = self::getItemsForInterval($firstItems, $firstIntervalOfDates, $firstCashFlowStatementDurationType, $firstMainItemName);
		$secondItems = self::getItemsForInterval($secondItems, $secondIntervalOfDates, $secondCashFlowStatementDurationType, $secondMainItemName);
		$firstIntervalDate  = $firstIntervalOfDates[0] . '/' . $firstIntervalOfDates[count($firstIntervalOfDates) - 1];
		$secondIntervalDate  = $secondIntervalOfDates[0] . '/' . $secondIntervalOfDates[count($secondIntervalOfDates) - 1];
		if (secondIntervalGreaterThanFirst($firstIntervalDate, $secondIntervalDate)) {
			return [
				'second-interval#' . $secondIntervalDate => sum_each_key($secondItems),
				'first-interval#' . $firstIntervalDate => sum_each_key($firstItems),
			];
		} else {
			return [
				'first-interval#' . $firstIntervalDate => sum_each_key($firstItems),
				'second-interval#' . $secondIntervalDate => sum_each_key($secondItems)
			];
		}
	}

	public static function _compareBetweenTwoItems(Collection $firstItems, array $firstIntervalOfDates, string $firstCashFlowStatementDurationType, string $firstReportType, Collection $secondItems, array $secondIntervalOfDates, string $secondCashFlowStatementDurationType, string $secondReportType, string $mainItemName, $sumInterval = true): array
	{
		$firstItems = self::getItemsForInterval($firstItems, $firstIntervalOfDates, $firstCashFlowStatementDurationType, $mainItemName);
		$secondItems = self::getItemsForInterval($secondItems, $secondIntervalOfDates, $secondCashFlowStatementDurationType, $mainItemName);
		$firstIntervalDate  = $firstIntervalOfDates[0] . '/' . $firstIntervalOfDates[count($firstIntervalOfDates) - 1];
		$secondIntervalDate  = $secondIntervalOfDates[0] . '/' . $secondIntervalOfDates[count($secondIntervalOfDates) - 1];
		if (secondReportIsFirstInArray($firstReportType, $secondReportType)) {

			return [
				$secondReportType . '#' . $secondIntervalDate => $sumInterval ? sum_each_key($secondItems) : $secondItems,
				$firstReportType . '#' . $firstIntervalDate => $sumInterval ? sum_each_key($firstItems) : $firstItems,
			];
		} else {

			return [
				$firstReportType . '#' . $firstIntervalDate => $sumInterval ? sum_each_key($firstItems) : $firstItems,
				$secondReportType . '#' . $secondIntervalDate => $sumInterval ? sum_each_key($secondItems) : $secondItems
			];
		}
	}

	public static function getItemsForInterval(Collection $items, array $dates, $intervalName, $mainItemName = ''): array
	{
		// $items must be a collection 

		$firstDate = Carbon::make($dates[\array_key_first($dates)]);
		$lastDate = Carbon::make($dates[\array_key_last($dates)]);


		$filteredItems = [];

		foreach ($items as $item) {
			$payload = (array)json_decode($item->payload);
			foreach ($payload as $payloadDate => $payloadItem) {
				$payloadDateFormatted = Carbon::make($payloadDate);

				if ($intervalName == 'annually' && yearInArray($payloadDate, $dates)) {

					$filteredItems[$item->sub_item_name ?: $mainItemName][$payloadDate] = $payloadItem;
				} elseif (dateIsBetweenTwoDates($payloadDateFormatted, $firstDate, $lastDate)) {

					$filteredItems[$item->sub_item_name ?: $mainItemName][$payloadDate] = $payloadItem;
				}
			}
		}
		return $filteredItems;
	}

	protected static function booted()
	{
		static::addGlobalScope(function (Builder $builder) {
			$builder->where('financial_statement_able_type', 'CashFlowStatement');
		});
	}
	public static function _generateChartsData(array $dates, array $chartItems, array $arrayOfData, string $mainItemName)
	{
		return getChartsData($chartItems, $dates, $arrayOfData, $mainItemName);
	}
	
	public function getId(){
		return $this->id ;
	}
	public function getName()
	{
		return $this->pivot->sub_item_name ;
	}
	public function getBalanceAmount()
	{
		return $this->pivot->payload ? array_sum((array)json_decode($this->pivot->payload)) :0 ;
	}
	public function getReceivableValueAtDate(string $date)
	{
		if(!$this->pivot->payload){
			return 0 ;
		}
		$payload = (array) json_decode($this->pivot->payload) ;
		return $payload[$date] ?? 0;
	}
	public function getType()
	{
		return $this->pivot->receivable_or_payment ;
	}
	// protected static function booted()
	// {
	// 	static::addGlobalScope(function (Builder $builder) {
	// 		$builder->where('type', 'CashFlowStatement');
	// 		->orderBy('ordered','asc');
	// 	});
	// }
	
}
