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

class  CashFlowStatementItem extends Model implements IFinancialStatementAbleItem
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


	// for database usage 
	// public static function getMainItems()
	// {
	// 	return [
	// 		'Sales Revenue' => [
	// 			'id' => $salesRevenueId = self::SALES_REVENUE_ID,
	// 			'hasSubItems' => true,
	// 			'has_depreciation_or_amortization' => false,
	// 			'is_main_for_all_calculations' => true // when it change , all remains rows in tables will also changes,
	// 			, 'is_sales_rate' => false
	// 		], 'Sales Growth Rate %' => [
	// 			'id' => self::SALES_GROWTH_RATE_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes,
	// 			, 'is_sales_rate' => false,
	// 			'depends_on' => [$salesRevenueId]
	// 		],
	// 		'Cost Of Goods / Service Sold' => [
	// 			'id' => $costOfGoodsId = self::COST_OF_GOODS_ID,
	// 			'hasSubItems' => true,
	// 			'has_depreciation_or_amortization' => true,
	// 			'is_main_for_all_calculations' => true // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false
	// 		],
	// 		'Cost Of Goods / Service Sold ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' => $costOfGoodsId = self::COST_OF_GOODS_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'has_depreciation_or_amortization' => false,
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'Gross Profit' => [
	// 			'id' => $grossProfitId = self::GROSS_PROFIT_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId, $costOfGoodsId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false
	// 		],
	// 		'Gross Profit ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' => self::GROSS_PROFIT_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'Marketing Expenses' => [
	// 			'id' => $marketExpensesId = self::MARKET_EXPENSES_ID,
	// 			'hasSubItems' => true,
	// 			'has_depreciation_or_amortization' => true,
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false

	// 		],
	// 		'Marketing Expenses ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' => self::MARKET_EXPENSES_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'Sales & Distribution Expenses' => [
	// 			'id' => $salesAndDistributionExpensesId = self::SALES_AND_DISTRIBUTION_EXPENSES_ID,
	// 			'hasSubItems' => true,
	// 			'has_depreciation_or_amortization' => true,
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false

	// 		],
	// 		'Sales & Distribution Expenses ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' => self::SALES_AND_DISTRIBUTION_EXPENSES_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'General Expenses' => [
	// 			'id' => $generalExpensesID = self::GENERAL_EXPENSES_ID,
	// 			'hasSubItems' => true,
	// 			'has_depreciation_or_amortization' => true,
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false

	// 		],
	// 		'General Expenses ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' =>  self::GENERAL_EXPENSES_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'Earning Before Interest Taxes Depreciation Amortization - EBITDA' => [
	// 			'id' => $earningBeforeInterestTaxesDepreciationAmortizationId = self::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$grossProfitId, $marketExpensesId, $salesAndDistributionExpensesId, $generalExpensesID],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false

	// 		],
	// 		'EBITDA ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' =>  self::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'Earning Before Interest Taxes - EBIT' => [
	// 			'id' => $earningBeforeInterestTaxesId = self::EARNING_BEFORE_INTEREST_TAXES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$earningBeforeInterestTaxesDepreciationAmortizationId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false

	// 		],
	// 		'EBIT ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' =>  self::EARNING_BEFORE_INTEREST_TAXES_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],

	// 		'Finance Income / (Expenses)' => [
	// 			'id' => $financialIncomeOrExpense = self::FINANCIAL_INCOME_OR_EXPENSE_ID,
	// 			'hasSubItems' => true,
	// 			'has_depreciation_or_amortization' => false,
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false

	// 		],
	// 		'Finance Income / (Expenses) ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' =>  self::FINANCIAL_INCOME_OR_EXPENSE_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'Earning Before Taxes - EBT' => [
	// 			'id' =>   $earningBeforeTaxesId = self::EARNING_BEFORE_TAXES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$financialIncomeOrExpense, $earningBeforeInterestTaxesId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false

	// 		],
	// 		'EBT ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' =>  self::EARNING_BEFORE_TAXES_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'Corporate Taxes' => [
	// 			'id' => $corporateTaxesID = self::CORPORATE_TAXES_ID,
	// 			'hasSubItems' => true,
	// 			'has_depreciation_or_amortization' => false,
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false

	// 		],
	// 		'Corporate Taxes ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' =>  self::CORPORATE_TAXES_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 		'Net Profit' => [
	// 			'id' => self::NET_PROFIT_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$corporateTaxesID, $earningBeforeTaxesId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => false
	// 		],
	// 		'Net Profit ' . self::PERCENTAGE_OF_SALES => [
	// 			'id' =>  self::NET_PROFIT_PERCENTAGE_OF_SALES_ID,
	// 			'hasSubItems' => false,
	// 			'has_depreciation_or_amortization' => false,
	// 			'depends_on' => [$salesRevenueId],
	// 			'is_main_for_all_calculations' => false // when it change , all remains rows in tables will also changes
	// 			, 'is_sales_rate' => true
	// 		],
	// 	];
	// }

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
