<?php

namespace App\Models;

use App\Interfaces\Models\Interfaces\IFinancialStatementAbleItem;
use App\Models\Traits\Accessors\BalanceSheetItemAccessor;
use App\Models\Traits\Relations\BalanceSheetItemRelation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class  BalanceSheetItem extends Model implements IFinancialStatementAbleItem
{
	protected $table = 'financial_statement_able_items';

	// Intangible Assets Gross Amount
	use  BalanceSheetItemRelation, BalanceSheetItemAccessor;
	const PERCENTAGE_OF_SALES = '[ % Of Sales ]';
	// must start from 1  not zero
	// Fixed Assets Gross Amount
	const FIXED_ASSETS_GROSS_AMOUNT_ID = 25;
	const FIXED_ASSETS_ACCUMULATED_DEPRECIATION_AMOUNT = 27;
	// const SALES_GROWTH_RATE_ID = 2;
	const NET_FIXED_ASSETS  = 29;
	// const COST_OF_GOODS_PERCENTAGE_OF_SALES_ID = 4;
	const INTANGIBLE_ASSETS_GROSS_AMOUNT = 31;
	// Intangible Assets Accumulated Depreciation Amount
	// const GROSS_PROFIT_PERCENTAGE_OF_SALES_ID = 6;
	const INTANGIBLE_ASSETS_ACCUMULATED_DEPRECIATION_AMOUNT = 33;
	// const MARKET_EXPENSES_PERCENTAGE_OF_SALES_ID = 8;
	// Net Intangible Assets
	const NET_INTANGIBLE_ASSETS = 35;
	// const SALES_AND_DISTRIBUTION_EXPENSES_PERCENTAGE_OF_SALES_ID = 10;
	// Other Long Term Assets
	const OTHER_LONG_TERM_ASSETS = 37;
	// const GENERAL_EXPENSES_PERCENTAGE_OF_SALES_ID = 12;
	const  TOTAL_LONG_TERM_ASSETS = 39;
	const  CASH_AND_BANKS_ID = 41;
	// const EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_PERCENTAGE_OF_SALES_ID = 14;
	// Customers Receivables And Checks
	const CUSTOMERS_RECEIVABLES_AND_CHECKS = 43;
	// const EARNING_BEFORE_INTEREST_TAXES_PERCENTAGE_OF_SALES_ID = 16;
	const INVENTORY = 45;
	// const FINANCIAL_INCOME_OR_EXPENSE_PERCENTAGE_OF_SALES_ID = 18;
	// Other Debtors
	const OTHER_DEBTORS = 47;
	const TOTAL_CURRENT_ASSETS = 49;
	const TOTAL_ASSETS = 51;
	// const EARNING_BEFORE_TAXES_PERCENTAGE_OF_SALES_ID = 20;
	// Suppliers Payables And Checks
	const SUPPLIERS_PAYABLES_AND_CHECKS = 53;
	// const CORPORATE_TAXES_PERCENTAGE_OF_SALES_ID = 22;
	// Short Term Banking Facilities
	const SHORT_TERM_BANKING_FACULTIES = 55;
	// const NET_PROFIT_PERCENTAGE_OF_SALES_ID = 24;
	// Current Portion Of Long Terms Banking Facilities
	const CURRENT_PORTION_OF_LONG_TERMS_BANKING_FACILITIES = 57;
	// Taxes And Social Insurance
	const TAXES_AND_SOCIAL_INSURANCE = 59;

	// Other Creditors
	const OTHER_CREDITORS = 61;
	const TOTAL_CURRENT_LIABILITY = 63;
	const WORKING_CAPITAL = 65;
	const TOTAL_INVESTMENT = 67;
	// Long Terms Banking Facilities
	const LONG_TERMS_BANKING_FACILITIES = 69;
	// Other Long Terms Liabilities
	//  Total Long Terms Liabilities 
	const TOTAL_LONG_TERMS_LIABILITIES = 71;
	const TOTAL_LIABILITIES = 73;

	//  Shareholders Equity 
	const SHAREHOLDERS_EQUITY = 75;

	protected $guarded = [
		'id'
	];

	public static function rateFieldsIds(): array
	{
		return [];
	}
	public static function percentageOfSalesRows(): array  // do not remove
	{
		return [];
	}

	public static function formattedViewForDashboard(): array
	{
		return BalanceSheetItem::where('for_interval_comparing', 1)->pluck('name', 'id')->toArray();
	}

	public static function compareBetweenTowItems(Collection $firstItems, array $firstIntervalOfDates, string $firstBalanceSheetDurationType, Collection $secondItems, array $secondIntervalOfDates, string $secondBalanceSheetDurationType): array
	{

		$firstItems = self::getItemsForInterval($firstItems, $firstIntervalOfDates, $firstBalanceSheetDurationType);
		$secondItems = self::getItemsForInterval($secondItems, $secondIntervalOfDates, $secondBalanceSheetDurationType);
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

	public static function _compareBetweenTwoItems(Collection $firstItems, array $firstIntervalOfDates, string $firstBalanceSheetDurationType, string $firstReportType, Collection $secondItems, array $secondIntervalOfDates, string $secondBalanceSheetDurationType, string $secondReportType): array
	{
		$firstItems = self::getItemsForInterval($firstItems, $firstIntervalOfDates, $firstBalanceSheetDurationType);
		$secondItems = self::getItemsForInterval($secondItems, $secondIntervalOfDates, $secondBalanceSheetDurationType);
		$firstIntervalDate  = $firstIntervalOfDates[0] . '/' . $firstIntervalOfDates[count($firstIntervalOfDates) - 1];
		$secondIntervalDate  = $secondIntervalOfDates[0] . '/' . $secondIntervalOfDates[count($secondIntervalOfDates) - 1];
		if (secondReportIsFirstInArray($firstReportType, $secondReportType)) {
			return [
				$secondReportType . '#' . $secondIntervalDate => sum_each_key($secondItems),
				$firstReportType . '#' . $firstIntervalDate => sum_each_key($firstItems),
			];
		} else {
			return [
				$firstReportType . '#' . $firstIntervalDate => sum_each_key($firstItems),
				$secondReportType . '#' . $secondIntervalDate => sum_each_key($secondItems)
			];
		}
	}

	public static function getItemsForInterval(Collection $items, array $dates, $intervalName): array
	{
		$firstDate = Carbon::make($dates[\array_key_first($dates)]);
		$lastDate = Carbon::make($dates[\array_key_last($dates)]);
		$filteredItems = [];
		foreach ($items as $item) {
			$payload = (array)json_decode($item->payload);
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

	protected static function booted()
	{
		static::addGlobalScope(function (Builder $builder) {
			$builder->where('financial_statement_able_type', 'BalanceSheet');
		});
	}
}
