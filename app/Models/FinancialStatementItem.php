<?php

namespace App\Models;

use App\Models\Traits\Accessors\FinancialStatementItemAccessor;
use App\Models\Traits\Relations\FinancialStatementItemRelation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class  FinancialStatementItem extends Model
{
	use  FinancialStatementItemRelation, FinancialStatementItemAccessor;
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

	public static function rateFieldsIds(): array
	{
		$inc = IncomeStatement::first();

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

	public static function formattedViewForDashboard(): array
	{
		return FinancialStatementItem::where('for_interval_comparing', 1)->pluck('name', 'id')->toArray();
	}

	public static function compareBetweenTowItems(Collection $firstItems, array $firstIntervalOfDates, string $firstFinancialStatementDurationType, Collection $secondItems, array $secondIntervalOfDates, string $secondFinancialStatementDurationType): array
	{

		$firstItems = self::getItemsForInterval($firstItems, $firstIntervalOfDates, $firstFinancialStatementDurationType);
		$secondItems = self::getItemsForInterval($secondItems, $secondIntervalOfDates, $secondFinancialStatementDurationType);
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
	public static function getItemsForInterval(Collection $items, array $dates, $intervalName): array
	{
		// $items must be a collection 

		$firstDate = Carbon::make($dates[\array_key_first($dates)]);
		$lastDate = Carbon::make($dates[\array_key_last($dates)]);

		$filteredItems = [];

		foreach ($items as $item) {
			$payload = json_decode($item->payload);
			foreach ($payload as $payloadDate => $payloadItem) {
				$payloadDateFormatted = Carbon::make($payloadDate);
				if ($intervalName == 'annually' && yearInArray($payloadDate, $dates)) {
					$filteredItems[$item->sub_item_name][$payloadDate] = $payloadItem;
				} elseif (
					dateIsBetweenTwoDates($payloadDateFormatted, $firstDate, $lastDate)
				) {
					$filteredItems[$item->sub_item_name][$payloadDate] = $payloadItem;
				}
			}
		}
		return $filteredItems;
	}
}
