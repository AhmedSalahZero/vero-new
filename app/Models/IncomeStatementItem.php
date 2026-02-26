<?php

namespace App\Models;

use App\Interfaces\Models\Interfaces\IFinancialStatementAbleItem;
use App\Models\Traits\Accessors\IncomeStatementItemAccessor;
use App\Models\Traits\Relations\IncomeStatementItemRelation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class  IncomeStatementItem extends Model implements IFinancialStatementAbleItem
{
	protected $table = 'financial_statement_able_items';

	use  IncomeStatementItemRelation, IncomeStatementItemAccessor;
	const PERCENTAGE_OF_SALES = '[ % Of Sales ]';
	// must start from 1  not zero
	const SALES_REVENUE_ID = 1;
	const SALES_GROWTH_RATE_ID = 2;
	const COST_OF_GOODS_ID = 3;
	const COST_OF_GOODS_PERCENTAGE_OF_SALES_ID = 4;
	const GROSS_PROFIT_ID = 5;
	const GROSS_PROFIT_PERCENTAGE_OF_SALES_ID = 6;
	const MARKET_EXPENSES_ID = 7;
	const MARKET_EXPENSES_PERCENTAGE_OF_SALES_ID = 8;
	const SALES_AND_DISTRIBUTION_EXPENSES_ID = 9;
	const SALES_AND_DISTRIBUTION_EXPENSES_PERCENTAGE_OF_SALES_ID = 10;
	const GENERAL_EXPENSES_ID = 11;
	const GENERAL_EXPENSES_PERCENTAGE_OF_SALES_ID = 12;
	const EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_ID = 13;
	const EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_PERCENTAGE_OF_SALES_ID = 14;
	const EARNING_BEFORE_INTEREST_TAXES_ID = 15;
	const EARNING_BEFORE_INTEREST_TAXES_PERCENTAGE_OF_SALES_ID = 16;
	const FINANCIAL_INCOME_OR_EXPENSE_ID = 17;
	const FINANCIAL_INCOME_OR_EXPENSE_PERCENTAGE_OF_SALES_ID = 18;
	const EARNING_BEFORE_TAXES_ID = 19;
	const EARNING_BEFORE_TAXES_PERCENTAGE_OF_SALES_ID = 20;
	const CORPORATE_TAXES_ID = 21;
	const CORPORATE_TAXES_PERCENTAGE_OF_SALES_ID = 22;
	const NET_PROFIT_ID = 23;
	const NET_PROFIT_PERCENTAGE_OF_SALES_ID = 24;

	protected $guarded = [
		'id'
	];
	// public static function isMainWithSubItems(Collection $allMainItems, int $incomeStatementItemId)
	// {

	// 	$hasSubItems = false;
	// 	foreach ($allMainItems as $mainItem) {
	// 		if ($mainItem->id == $incomeStatementItemId && $mainItem->has_sub_items) {
	// 			$hasSubItems = true;
	// 		}
	// 	}
	// 	return $hasSubItems;
	// }
	// public static function isMainWithoutSubItems(collection $allMainItems, int $incomeStatementItemId, bool $isPercentageOfSales)
	// {
	// 	$hasNotSubItems = false;
	// 	foreach ($allMainItems as $mainItem) {
	// 		if ($mainItem->id == $incomeStatementItemId && !$mainItem->has_sub_items && !$isPercentageOfSales) {
	// 			$hasNotSubItems = true;
	// 		}
	// 	}
	// 	return $hasNotSubItems;
	// }
	public static function rateFieldsIds(): array
	{
		return [
			self::SALES_GROWTH_RATE_ID,
			self::COST_OF_GOODS_PERCENTAGE_OF_SALES_ID,
			self::GROSS_PROFIT_PERCENTAGE_OF_SALES_ID,
			self::MARKET_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::SALES_AND_DISTRIBUTION_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::GENERAL_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_INTEREST_TAXES_PERCENTAGE_OF_SALES_ID,
			self::FINANCIAL_INCOME_OR_EXPENSE_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_TAXES_PERCENTAGE_OF_SALES_ID,
			self::CORPORATE_TAXES_PERCENTAGE_OF_SALES_ID,
			self::NET_PROFIT_PERCENTAGE_OF_SALES_ID,
		];
	}
	public static function getEquationFor($financialStatementAble, int $incomeStatementItemId)
	{
		$incomeStatementItem = IncomeStatementItem::find($incomeStatementItemId);
		if ($financialStatementAble->type == 'CashFlowStatement') {
			$incomeStatementItem = CashFlowStatementItem::find($incomeStatementItemId);
		} elseif ($financialStatementAble->type == 'BalanceSheet') {
			$incomeStatementItem = BalanceSheetItem::find($incomeStatementItemId);
		}
		return $incomeStatementItem->equation;
	}
	public static function isPercentageOfSalesRevenue(int $incomeStatementItemId): bool
	{
		return in_array($incomeStatementItemId, self::percentageOfSalesRows());
	}
	public static function percentageOfSalesRows(): array
	{
		return [
			self::COST_OF_GOODS_PERCENTAGE_OF_SALES_ID,
			self::GROSS_PROFIT_PERCENTAGE_OF_SALES_ID,
			self::MARKET_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::SALES_AND_DISTRIBUTION_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::GENERAL_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_INTEREST_TAXES_PERCENTAGE_OF_SALES_ID,
			self::FINANCIAL_INCOME_OR_EXPENSE_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_TAXES_PERCENTAGE_OF_SALES_ID,
			self::CORPORATE_TAXES_PERCENTAGE_OF_SALES_ID,
			self::NET_PROFIT_PERCENTAGE_OF_SALES_ID,
		];
	}
	public static function salesRateMap(): array
	{
		return [
			self::COST_OF_GOODS_ID => self::COST_OF_GOODS_PERCENTAGE_OF_SALES_ID,
			self::GROSS_PROFIT_ID => self::GROSS_PROFIT_PERCENTAGE_OF_SALES_ID,
			self::MARKET_EXPENSES_ID => self::MARKET_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::SALES_AND_DISTRIBUTION_EXPENSES_ID => self::SALES_AND_DISTRIBUTION_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::GENERAL_EXPENSES_ID => self::GENERAL_EXPENSES_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_ID => self::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_INTEREST_TAXES_ID => self::EARNING_BEFORE_INTEREST_TAXES_PERCENTAGE_OF_SALES_ID,
			self::FINANCIAL_INCOME_OR_EXPENSE_ID => self::FINANCIAL_INCOME_OR_EXPENSE_PERCENTAGE_OF_SALES_ID,
			self::EARNING_BEFORE_TAXES_ID => self::EARNING_BEFORE_TAXES_PERCENTAGE_OF_SALES_ID,
			self::CORPORATE_TAXES_ID => self::CORPORATE_TAXES_PERCENTAGE_OF_SALES_ID,
			self::NET_PROFIT_ID => self::NET_PROFIT_PERCENTAGE_OF_SALES_ID,
		];
	}
	
	public static function vatRatesMap(): array
	{
		$data= [];
		IncomeStatementItem::get()->each(function($item) use(&$data){
				$data[$item->id] = [
					'has_vat_rate'=>$item->has_vat_rate,
					'can_be_dedictiable'=>$item->can_be_dedictiable
				];
		});
		return $data ; 
	}

	public static function formattedViewForDashboard(): array
	{
		return IncomeStatementItem::where('for_interval_comparing', 1)->pluck('name', 'id')->toArray();
	}

	public static function compareBetweenTowItems(Collection $firstItems, array $firstIntervalOfDates, string $firstIncomeStatementDurationType, Collection $secondItems, array $secondIntervalOfDates, string $secondIncomeStatementDurationType, ?string $firstMainItemName, ?string $secondMainItemName): array
	{


		$firstItems = self::getItemsForInterval($firstItems, $firstIntervalOfDates, $firstIncomeStatementDurationType, $firstMainItemName);
		$secondItems = self::getItemsForInterval($secondItems, $secondIntervalOfDates, $secondIncomeStatementDurationType, $secondMainItemName);
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

	public static function _compareBetweenTwoItems(Collection $firstItems, array $firstIntervalOfDates, string $firstIncomeStatementDurationType, string $firstReportType, Collection $secondItems, array $secondIntervalOfDates, string $secondIncomeStatementDurationType, string $secondReportType, string $mainItemName, $sumInterval = true): array
	{
		$firstItems = self::getItemsForInterval($firstItems, $firstIntervalOfDates, $firstIncomeStatementDurationType, $mainItemName);
		$secondItems = self::getItemsForInterval($secondItems, $secondIntervalOfDates, $secondIncomeStatementDurationType, $mainItemName);
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
		$firstDateAsIndex = array_key_first($dates);
		$lastDateAsIndex = array_key_last($dates);
		
		$filteredItems = [];
		foreach ($items as $item) {
			$payload = (array)json_decode($item->payload);
			foreach ($payload as $payloadDate => $payloadItem) {
				if ($intervalName == 'annually' && yearInArray($dates[$payloadDate], $dates)) {
					$filteredItems[$item->sub_item_name ?: $mainItemName][$payloadDate] = $payloadItem;
				} elseif ($payloadDate>=$firstDateAsIndex && $payloadDate<=$lastDateAsIndex) {
					$filteredItems[$item->sub_item_name ?: $mainItemName][$payloadDate] = $payloadItem;
				}
			}
		}
		return $filteredItems;
	}

	protected static function booted()
	{
		static::addGlobalScope(function (Builder $builder) {
			$builder->where('financial_statement_able_type', 'IncomeStatement');
		});
	}
	public static function _generateChartsData(array $dates, array $chartItems, array $arrayOfData, string $mainItemName)
	{
		return getChartsData($chartItems, $dates, $arrayOfData, $mainItemName);
	}
}
