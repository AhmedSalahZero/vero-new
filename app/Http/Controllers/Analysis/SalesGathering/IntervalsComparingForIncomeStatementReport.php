<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class IntervalsComparingForIncomeStatementReport
{
	use GeneralFunctions;


	public function result(Request $request, Company $company, array $intervalDates, string $firstReportType, string $secondReportType)
	{
		$start_date_one  = $intervalDates['first_start_date'];
		$end_date_one  = $intervalDates['first_end_date'];

		$start_date_two  = $intervalDates['second_start_date'];
		$end_date_two  = $intervalDates['second_end_date'];

		// $start_date_three  = $request->start_date_three;
		// $end_date_three  = $request->end_date_three;
		$type = $request->type;
		$view_name = $request->view_name;




		$dates = [
			'start_date_one' => date('d-M-Y', strtotime($start_date_one)),
			'end_date_one' => date('d-M-Y', strtotime($end_date_one)),
			'start_date_two' => date('d-M-Y', strtotime($start_date_two)),
			'end_date_two' => date('d-M-Y', strtotime($end_date_two)),
			//  'start_date_three' => date('d-M-Y',strtotime($start_date_three)),
			// 'end_date_three' => date('d-M-Y',strtotime($end_date_three))
		];

		// First_interval
		$request['start_date'] = $start_date_one;
		$request['end_date'] = $end_date_one;
		$firstIntervalDates = generateDatesBetweenTwoDates(Carbon::make($start_date_one), Carbon::make($end_date_one));
		$result_for_interval_one = [];



		// Second_interval
		$request['start_date'] = $start_date_two;
		$request['end_date'] = $end_date_two;

		$secondIntervalDates = generateDatesBetweenTwoDates(Carbon::make($start_date_two), Carbon::make($end_date_two));




		$firstIncomeStatementId = $request->get('financial_statement_able_first_interval');
		$secondIncomeStatementId = $request->get('financial_statement_able_second_interval');
		$firstIncomeStatement = IncomeStatement::find($firstIncomeStatementId) ?: IncomeStatement::where('company_id', $company->id)->latest()->first();
		$secondIncomeStatement = IncomeStatement::find($secondIncomeStatementId) ?: IncomeStatement::where('company_id', $company->id)->latest()->first();
		$incomeStatementItemId = $request->get('mainItemId');
		if (!$firstIncomeStatement || !$secondIncomeStatement) {
			return [];
		}
		$firstIncomeStatementId = $firstIncomeStatement->id;
		$secondIncomeStatementId = $secondIncomeStatement->id;
		$firstMainItem = $firstIncomeStatement->withMainItemsFor($incomeStatementItemId)->first();
		$firstIncomeStatementItemSubItemsPivot = $firstMainItem->has_sub_items ? $firstMainItem->getSubItemsPivot($firstIncomeStatementId, $firstReportType) : $firstMainItem->getMainRowsPivot($firstIncomeStatementId, $firstReportType);
		$firstIncomeStatementItemSubItemsPivot = count($firstIncomeStatementItemSubItemsPivot) ? $firstIncomeStatementItemSubItemsPivot : $firstMainItem->getMainRowsPivot($firstIncomeStatementId, $firstReportType);
		$secondMainItem = $secondIncomeStatement->withMainItemsFor($incomeStatementItemId)->first();
		$firstIncomeStatementDurationType = $firstIncomeStatement->duration_type;
		$secondIncomeStatementItemSubItemsPivot = count($secondMainItem->getSubItemsPivot($secondIncomeStatementId, $secondReportType))  ? $secondMainItem->getSubItemsPivot($secondIncomeStatementId, $secondReportType) : $secondMainItem->getMainRowsPivot($secondIncomeStatementId, $secondReportType);
		$secondIncomeStatementItemSubItemsPivot = count($secondIncomeStatementItemSubItemsPivot) ? $secondIncomeStatementItemSubItemsPivot : collect([]);
		$secondIncomeStatementDurationType = $secondIncomeStatement->duration_type;
		$firstMainItemName = $firstMainItem->name;
		$secondMainItemName = $secondMainItem->name;
		$report_result = IncomeStatementItem::compareBetweenTowItems($firstIncomeStatementItemSubItemsPivot, $firstIntervalDates, $firstIncomeStatementDurationType, $secondIncomeStatementItemSubItemsPivot, $secondIntervalDates, $secondIncomeStatementDurationType, $firstMainItemName, $secondMainItemName);
		return $report_result;
	}


	public function resultVariousComparing(Request $request, Company $company, array $intervalDates, string $firstReportType, string $secondReportType, bool $chartHasBeenFound, string $mainItemName, array $chartItems)
	{


		$start_date  = $intervalDates['start_date'];
		$end_date  = $intervalDates['end_date'];


	

		// First_interval
		$request['start_date'] = $start_date;
		$request['end_date'] = $end_date;
		$firstIntervalDates = generateDatesBetweenTwoDates(Carbon::make($start_date), Carbon::make($end_date));

		// Second_interval


		$firstIncomeStatementId = $request->get('income_statement_id');
		$secondIncomeStatementId = $request->get('income_statement_id');
		$firstIncomeStatement = IncomeStatement::find($firstIncomeStatementId) ?: IncomeStatement::where('company_id', $company->id)->latest()->first();
		$secondIncomeStatement = $firstIncomeStatement;
		$incomeStatementItemId = $request->get('mainItemId');
		if (!$firstIncomeStatement || !$secondIncomeStatement) {
			return [];
		}
		$firstIncomeStatementId = $firstIncomeStatement->id;
		$secondIncomeStatementId = $secondIncomeStatement->id;
		$firstMainItem = $firstIncomeStatement->withMainItemsFor($incomeStatementItemId)->first();
		$firstIncomeStatementItemSubItemsPivot = $firstMainItem->has_sub_items ? $firstMainItem->getSubItemsPivot($firstIncomeStatementId, $firstReportType) : $firstMainItem->getMainRowsPivot($firstIncomeStatementId, $firstReportType);
		$firstIncomeStatementItemSubItemsPivot = count($firstIncomeStatementItemSubItemsPivot) ? $firstIncomeStatementItemSubItemsPivot : $firstMainItem->getMainRowsPivot($firstIncomeStatementId, $firstReportType);
		$secondMainItem = $firstMainItem;
		$firstIncomeStatementDurationType = $firstIncomeStatement->duration_type;
		$secondIncomeStatementItemSubItemsPivot = count($secondMainItem->getSubItemsPivot($secondIncomeStatementId, $secondReportType))  ? $secondMainItem->getSubItemsPivot($secondIncomeStatementId, $secondReportType) : $secondMainItem->getMainRowsPivot($secondIncomeStatementId, $secondReportType);
		$secondIncomeStatementItemSubItemsPivot = count($secondIncomeStatementItemSubItemsPivot) ? $secondIncomeStatementItemSubItemsPivot : collect([]);
		$secondIncomeStatementDurationType = $secondIncomeStatement->duration_type;
		$secondIntervalDates = $firstIntervalDates;
		$report_result = IncomeStatementItem::_compareBetweenTwoItems($firstIncomeStatementItemSubItemsPivot, $firstIntervalDates, $firstIncomeStatementDurationType, $firstReportType, $secondIncomeStatementItemSubItemsPivot, $secondIntervalDates, $secondIncomeStatementDurationType, $secondReportType, $mainItemName, true);
		$report_result_for_charts = IncomeStatementItem::_compareBetweenTwoItems($firstIncomeStatementItemSubItemsPivot, $firstIntervalDates, $firstIncomeStatementDurationType, $firstReportType, $secondIncomeStatementItemSubItemsPivot, $secondIntervalDates, $secondIncomeStatementDurationType, $secondReportType, $mainItemName, false);
		$charts = [];
		$charts = IncomeStatementItem::_generateChartsData($firstIntervalDates, $chartItems, $report_result_for_charts, $mainItemName);
		return [
			'report' => $report_result,
			'charts' => $charts ?? [],
			'dates' => $firstIntervalDates
		];
	}
}
