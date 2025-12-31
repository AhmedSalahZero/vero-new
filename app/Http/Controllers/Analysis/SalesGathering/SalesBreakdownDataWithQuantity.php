<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesBreakdownDataWithQuantity
{
	public function salesBreakdownAnalysisResult(Request $request, Company $company, $result = 'view', $num_of_years = 1)
	{
		$dimension = $request->report_type;
		$report_data = [];
		$report_view_data = [];
		$growth_rate_data = [];
		$report_count_data = [];

		$type = $request->type;

		$dates = [
			'start_date' => date('d-M-Y', strtotime($request->start_date)),
			'end_date' => date('d-M-Y', strtotime($request->end_date))
		];



		$view_name = $request->view_name;
		//   isset($calculated_report_data) ? $calculated_report_data :
		$report_data =  collect(DB::select(DB::raw(
			"
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value,service_provider_name,quantity," . $type . "
                FROM sales_gathering
                force index (sales_channel_index)
                WHERE ( company_id = '" . $company->id . "'AND " . $type . " IS NOT NULL  AND date between '" . $request->start_date . "' and '" . $request->end_date . "')

                ORDER BY id "
		)));


		if ($type == 'service_provider_birth_year' || $type == 'service_provider_type') {
			$data = $report_data->groupBy($type)->map(function ($item, $year) {
				return  $item->groupBy('service_provider_name')->flatMap(function ($sub_item, $name) use ($item, $year) {
					return [
						$name => $sub_item->sum('net_sales_value'),
					];
				});
			})->toArray();
			if ($type == 'service_provider_birth_year') {


				$report_view_data = [
					0 => [
						'item' =>  'Age Range Less Than 40',
						'Sales Value' =>  0,
					],
					1 => [
						'item' =>  'Age Range 41 - 50',
						'Sales Value' =>  0,
					],
					2 => [
						'item' =>  'Age Range 51 - 60',
						'Sales Value' =>  0,
					],
					3 => [
						'item' =>  'Age Range Over 60',
						'Sales Value' =>  0,
					],
				];
				$report_count_data = [
					0 => [
						'item' =>  'Age Range Less Than 40',
						'Count' =>  0,
					],
					1 => [
						'item' =>  'Age Range 41 - 50',
						'Count' =>  0,
					],
					2 => [
						'item' =>  'Age Range 51 - 60',
						'Count' =>  0,
					],
					3 => [
						'item' =>  'Age Range Over 60',
						'Count' =>  0,
					],
				];
				$key = 0;
				$current_date = date('Y');
				foreach ($data as $year => $data_per_year) {
					$age = $current_date - $year;
					if ($age <= 40) {
						$key = 0;
					} elseif ($age >= 41 && $age <= 50) {
						$key =  1;
					} elseif ($age >= 51 && $age <= 60) {
						$key =  2;
					} elseif ($age >  60) {
						$key =  3;
					}
					$report_view_data[$key]['Sales Value'] = ($report_view_data[$key]['Sales Value'] ?? 0) + array_sum(($data_per_year ?? []));
					$report_count_data[$key]['Count'] = ($report_count_data[$key]['Count'] ?? 0) + count(($data_per_year ?? []));
				}
			} else {
				$key = 0;
				$report_data = [];
				foreach ($data as $type_name => $data_per_year) {
					$report_data[$key]['item'] = $type_name;
					$report_data[$key]['Sales Value'] = ($report_data[$key]['Sales Value'] ?? 0) + array_sum(($data_per_year ?? []));
					$report_count_data[$key]['item'] = $type_name;
					$report_count_data[$key]['Count'] = ($report_count_data[$key]['Count'] ?? 0) + count(($data_per_year ?? []));
					$key++;
				}
			}
		} else {

			$key_num = 0;
			$others = 0;
			$total_sales_values = $report_data->sum('net_sales_value');

			$report_data = $report_data->groupBy($type)->flatMap(function ($item, $name) use ($num_of_years, $total_sales_values) {
				$total_net_sales_value = $item->sum('net_sales_value');
				$total_quantity = $item->sum('quantity');
				return   [[
					'item' => $name,
					'Sales Value' => $num_of_years ? $total_net_sales_value / $num_of_years : 0,
					'Sales Quantity' => $num_of_years ? $total_quantity / $num_of_years : 0,
					'Sales %' => $total_sales_values == 0 ? 0 : ((($total_net_sales_value / $num_of_years) / $total_sales_values) * 100),
					'Average Price' => $total_quantity ? $total_net_sales_value / $total_quantity  : 0

				]];
			})->toArray();
		}

		if ((count($report_data) > 0) && ($type !== 'service_provider_birth_year') && $result !== "withOthers") {

			$key_num = 0;
			$report_data =  collect($report_data)->sortByDesc(function ($data, $key) use ($key_num) {
				return [($data['Sales Value'])];
			});
			$viewing_data = $report_data->toArray();
			$total_of_all_data = array_sum(array_column($viewing_data, 'Sales Value'));
			if ($request->get('direction') == 'asc') {
				$report_data  = $report_data->reverse();
			}
			$top_100 = $report_data->take(100);


			$others_count = count($report_data) - count($top_100);
			$report_view_data = $top_100->toArray();
			$others_total = $total_of_all_data - array_sum(array_column($report_view_data, 'Sales Value'));
			if ($others_total > 0) {

				array_push($report_view_data, [
					'item' => 'Others ' . $others_count, 'Sales Value' => $others_total,
					'Sales %' => $total_sales_values == 0 ? 0 : ((($others_total / $num_of_years) / $total_sales_values) * 100),
				]);
			}
			$key_num = 0;
			$final_data = [];

			array_walk($report_view_data, function ($value, $key) use (&$key_num, &$final_data) {
				$final_data[$key_num] = $value;
				$key_num++;
			});
			$report_view_data = $final_data;
		}
		if ($result == 'view') {
			if (count($report_data) == 0) {
				toastr()->error('No Data Found');
				return redirect()->back();
			}

			$last_date = null;
			// Last Date
			$last_date = DB::table('sales_gathering')->where('company_id', $company->id)->latest('date')->first()->date;
			$last_date = date('d-M-Y', strtotime($last_date));

			return view('client_view.reports.sales_gathering_analysis.breakdown.sales_report', compact('last_date', 'report_count_data', 'type', 'view_name', 'dates', 'company', 'report_view_data'));
		} elseif ($result == "withOthers") {
			return $report_data;
		} else {

			if ($type == 'service_provider_birth_year' || $type == 'service_provider_type') {
				return   [
					'report_count_data' => $report_count_data,
					'report_view_data' => $report_view_data
				];
			} else {




				return $report_view_data;
			}
		}
	}
}
