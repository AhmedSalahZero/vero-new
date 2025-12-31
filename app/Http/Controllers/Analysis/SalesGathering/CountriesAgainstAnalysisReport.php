<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountriesAgainstAnalysisReport
{
	use GeneralFunctions;
	public function index(Company $company)
	{

		if (request()->route()->named('country.zones.analysis')) {
			$type = 'zone';
			$view_name = 'Countries Against Zones Trend Analysis';
		} elseif (request()->route()->named('country.salesChannels.analysis')) {
			$type  = 'sales_channel';
			$view_name = 'Countries Against Sales Channels Trend Analysis';
		} elseif (request()->route()->named('country.customers.analysis')) {
			$type = 'customer_name';
			$view_name = 'Countries Against Customers Trend Analysis';
		} elseif (request()->route()->named('country.categories.analysis')) {
			$type  = 'category';
			$view_name = 'Countries Against Categories Trend Analysis';
		} elseif (request()->route()->named('country.products.analysis')) {
			$type  = 'product_or_service';
			$view_name = 'Countries Against Products / Services Trend Analysis';
		} elseif (request()->route()->named('country.principles.analysis')) {
			$type  = 'principle';
			$view_name = 'Countries Against Principles Trend Analysis';
		} elseif (request()->route()->named('country.Items.analysis')) {
			$type  = 'product_item';
			$view_name = 'Countries Against Products Items Trend Analysis';
		} elseif (request()->route()->named('country.salesPersons.analysis')) {
			$type  = 'sales_person';
			$view_name = 'Countries Against Sales Persons Trend Analysis';
		} elseif (request()->route()->named('country.salesDiscount.analysis')) {
			$type  = 'quantity_discount';
			$view_name = 'Countries Against Sales Discount Trend Analysis';
		} elseif (request()->route()->named('country.businessSectors.analysis')) {
			$type  = 'business_sector';
			$view_name = 'Countries Against Business Sectors Trend Analysis';
		}elseif (request()->route()->named('country.businessUnits.analysis')) {
			$type  = 'business_unit';
			$view_name = 'Countries Against Business Units Trend Analysis';
		} elseif (request()->route()->named('country.branches.analysis')) {
			$type  = 'branch';
			$view_name = 'Countries Against Branches Trend Analysis';
		} elseif (request()->route()->named('country.products.averagePrices')) {
			$type  = 'averagePrices';
			$view_name = 'Countries Products / Services Average Prices';
		} elseif (request()->route()->named('country.Items.averagePrices')) {
			$type  = 'averagePricesProductItems';
			$view_name = 'Countries Products Items Average Prices';
		}

		$name_of_selector_label = str_replace(['Countries Against ', ' Trend Analysis'], '', $view_name);

		if ($type == 'averagePrices') {
			$name_of_selector_label = 'Products / Services';
		} elseif ($type  == 'averagePricesProductItems') {
			$name_of_selector_label = 'Products Items';
		}



		return view('client_view.reports.sales_gathering_analysis.countries_analysis_form', compact('company', 'name_of_selector_label', 'type', 'view_name'));
	}
	public function CountriesSalesAnalysisIndex(Company $company)
	{
		// Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
		$selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
		return view('client_view.reports.sales_gathering_analysis.countries_sales_form', compact('company', 'selected_fields'));
	}
	public function result(Request $request, Company $company, $result = 'view')
	{
		$report_data = [];
		$report_data_quantity = [];
		$growth_rate_data = [];
		$countries_names = [];
		$final_report_total = [];
		$mainData =
			$countries = is_array(json_decode(($request->countries[0]))) ? json_decode(($request->countries[0])) : $request->countries;
		$type = $request->type;
		$view_name = $request->view_name;
		$name_of_report_item  = ($result == 'view') ? 'Sales Values' : 'Avg. Prices';
		$data_type = ($request->data_type === null || $request->data_type == 'value') ? 'net_sales_value' : 'quantity';
		foreach ($mainData as  $main_row) {
			if ($result == 'view' || $result == 'data') {
				$main_row = str_replace("'", "''", $main_row);
				$mainData_data = collect(DB::select(DB::raw(
					"
                        SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  ,  " . $data_type . " ,country," . $type . "
                        FROM sales_gathering
                        WHERE ( company_id = '" . $company->id . "'AND country = '" . $main_row . "' AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
                        ORDER BY id"
				)))->groupBy($type)->map(function ($item) use ($data_type) {
					return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {

						return $sub_item->sum($data_type);
					});
				})->toArray();
			} else {
				$mainData_data = DB::table('sales_gathering')
					->where('company_id', $company->id)
					->where('country', $main_row)
					->whereNotNull($type)
					->whereBetween('date', [$request->start_date, $request->end_date])
					->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,
                    (IFNULL(' . $data_type . ',0) ) as ' . $data_type . ' ,country,' . $type)
					->get()
					->groupBy($type)->map(function ($item) use ($data_type) {
						return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {
							return $sub_item->sum($data_type);
						});
					})->toArray();



				$report_data_quantity = DB::table('sales_gathering')
					->where('company_id', $company->id)
					->where('country', $main_row)
					->whereNotNull($type)
					->whereBetween('date', [$request->start_date, $request->end_date])
					->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,
                    (IFNULL(' . $data_type . ',0) ) as ' . $data_type . ' , IFNULL(quantity_bonus,0) quantity_bonus , IFNULL(quantity,0) quantity,country,' . $type)
					->get()
					->groupBy($type)->map(function ($item) use ($data_type) {
						return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {
							return ($sub_item->sum('quantity_bonus') + $sub_item->sum('quantity'));
						});
					})->toArray();
			}
			foreach ($request->sales_channels as $sales_channel_key => $sales_channel) {

				$data_per_main_item = $mainData_data[$sales_channel] ?? [];

				$years = [];
				if (count(($data_per_main_item)) > 0) {


					// Data & Growth Rate Per Sales Channel
					array_walk($data_per_main_item, function ($val, $date) use (&$years) {
						$years[] = date('Y', strtotime($date));
					});
					$years = array_unique($years);

					$report_data[$main_row][$sales_channel][$name_of_report_item] = $data_per_main_item;
					$interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data[$main_row][$sales_channel], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);

					$report_data[$main_row][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];

					$report_data[$main_row]['Total']  = $this->finalTotal([($report_data[$main_row]['Total']  ?? []), ($report_data[$main_row][$sales_channel][$name_of_report_item] ?? [])]);
					$report_data[$main_row][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data[$main_row][$sales_channel][$name_of_report_item] ?? []));
				}
			}

			if ($result == 'array') {

				foreach ($request->sales_channels ?? [] as $sales_channel_key => $sales_channel) {

					$years_quantity = [];
					$data_per_main_item = $report_data_quantity[$sales_channel] ?? [];

					if (count(($data_per_main_item)) > 0) {


						// Data & Growth Rate Per Sales Channel
						array_walk($data_per_main_item, function ($val, $date) use (&$years_quantity) {
							$years_quantity[] = date('Y', strtotime($date));
						});
						$years_quantity = array_unique($years_quantity);

						$report_data_quantity[$main_row][$sales_channel][$name_of_report_item] = $data_per_main_item;
						$interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data_quantity[$main_row][$sales_channel], $years_quantity, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
						$report_data_quantity[$main_row][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];

						$report_data_quantity[$main_row]['Total']  = $this->finalTotal([($report_data_quantity[$main_row]['Total']  ?? []), ($report_data_quantity[$main_row][$sales_channel][$name_of_report_item] ?? [])]);
						$report_data_quantity[$main_row][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data_quantity[$main_row][$sales_channel][$name_of_report_item] ?? []));
					}
				}




				foreach ($report_data as $reportType => $dates) {
					// Baby 20

					if ($main_row == $reportType) {
						foreach ($dates as $dateName => $items) {
							if ($dateName != 'Total') {
								//Avg. Prices
								foreach ($items as $itemKey => $values) {
									if ($itemKey == 'Avg. Prices') {
										foreach ($values as $datee => $dateVal) {

											$report_data[$reportType][$dateName][$itemKey][$datee] =
												$report_data_quantity[$reportType][$dateName][$itemKey][$datee] ?
												$report_data[$reportType][$dateName][$itemKey][$datee] / $report_data_quantity[$reportType][$dateName][$itemKey][$datee]
												: 0;

											$report_data[$reportType]['Totals'][$datee] = $report_data[$reportType][$dateName][$itemKey][$datee] + ($report_data[$reportType]['Totals'][$datee] ?? 0);



											$report_data[$reportType]['Total'][$datee] = $report_data[$reportType]['Totals'][$datee];
										}
									} elseif ($itemKey == 'Growth Rate %') {
										foreach ($values as $datee => $dateVal) {
											$report_data[$reportType][$dateName]['Avg. Prices'][$datee];
											$keys = array_flip(array_keys($report_data[$reportType][$dateName]['Avg. Prices']));
											$values = array_values($report_data[$reportType][$dateName]['Avg. Prices']);
											$previousValue = isset($values[$keys[$datee] - 1]) ? $values[$keys[$datee] - 1] : 0;


											$report_data[$reportType][$dateName][$itemKey][$datee] =  $previousValue ? (($report_data[$reportType][$dateName]['Avg. Prices'][$datee] - $previousValue) / $previousValue) * 100 : 0;
										}
									}
								}
							}
						}
					}
				}
			}
			// Total & Growth Rate Per Zone

			$final_report_total = $this->finalTotal([($report_data[$main_row]['Total'] ?? []), ($final_report_total ?? [])]);
			$report_data[$main_row]['Growth Rate %'] =  $this->growthRate(($report_data[$main_row]['Total'] ?? []));
			$countries_names[] = (str_replace(' ', '_', $main_row));
		}

		foreach ($report_data as $r => $d) {
			unset($report_data[$r]['Totals']);
		}


		// Total Zones & Growth Rate

		$report_data['Total'] = $final_report_total;
		$report_data['Growth Rate %'] =  $this->growthRate($report_data['Total']);
		$dates = array_keys($report_data['Total']);
		// $dates = formatDateVariable($dates, $request->start_date, $request->end_date);
		if ($result == 'view') {
			return view('client_view.reports.sales_gathering_analysis.countries_analysis_report', compact('company', 'name_of_report_item', 'view_name', 'countries_names', 'dates', 'report_data',));
		} else {
			return ['report_data' => $report_data, 'view_name' => $view_name, 'names' => $countries_names];
		}
	}

	public function resultForSalesDiscount(Request $request, Company $company)
	{

		$report_data = [];
		$final_report_data = [];
		$growth_rate_data = [];
		$zones_names = [];
		$sales_values = [];
		$sales_years = [];
		$zones = is_array(json_decode(($request->countries[0]))) ? json_decode(($request->countries[0])) : $request->countries;
		$type = $request->type;
		$view_name = $request->view_name;
		$zones_discount = [];


		$fields = '';
		foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
			$fields .= $sales_discount_field . ',';
		}


		foreach ($zones as  $zone) {

			$sales = collect(DB::select(DB::raw(
				"
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields . " country
                FROM sales_gathering
                WHERE ( company_id = '" . $company->id . "'AND country = '" . $zone . "' AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
                ORDER BY id"
			)))->groupBy('gr_date');
			$sales_values_per_zone[$zone] = $sales->map(function ($sub_item) {
				return $sub_item->sum('sales_value');
			})->toArray();



			foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
				$zones_discount = $sales->map(function ($sub_item) use ($sales_discount_field) {
					return $sub_item->sum($sales_discount_field);
				})->toArray();

				$zones_sales_values = [];
				$zones_per_month = [];
				$zones_data = [];
				$discount_years = [];

				if (@count($zones_discount) > 0) {

					// Data & Growth Rate Per Sales Channel


					array_walk($zones_discount, function ($val, $date) use (&$discount_years) {
						$discount_years[] = date('Y', strtotime($date));
					});
					$discount_years = array_unique($discount_years);

					array_walk($zones_sales_values, function ($val, $date) use (&$sales_years) {
						$sales_years[] = date('Y', strtotime($date));
					});
					$sales_years = array_unique($sales_years);



					$interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$sales_values_per_zone, $sales_years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);

					$sales_values[$zone]  = $interval_data['data_intervals'][$request->interval][$zone] ?? [];




					$final_report_data[$zone][$sales_discount_field]['Values'] = $zones_discount;
					$interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$final_report_data[$zone][$sales_discount_field], $discount_years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
					$final_report_data[$zone][$sales_discount_field] = $interval_data['data_intervals'][$request->interval] ?? [];


					$final_report_data[$zone]['Total']  = $this->finalTotal([($final_report_data[$zone]['Total']  ?? []), ($final_report_data[$zone][$sales_discount_field]['Values'] ?? [])]);






					$final_report_data['Total'] = $this->finalTotal([($final_report_data['Total'] ?? []), (($final_report_data[$zone][$sales_discount_field]['Values'] ?? []))]);


					$final_report_data[$zone][$sales_discount_field]['Perc.% / Sales'] = $this->operationAmongTwoArrays(($final_report_data[$zone][$sales_discount_field]['Values'] ?? []), ($sales_values[$zone] ?? []));
				}
			}
			$zones_names[] = (str_replace(' ', '_', $zone));
		}

		$sales_values = $this->finalTotal([$sales_values ?? []]);
		$total = $final_report_data['Total'];
		unset($final_report_data['Total']);
		$final_report_data['Total'] = $total;
		$final_report_data['Discount % / Total Sales'] = $this->operationAmongTwoArrays($final_report_data['Total'], $sales_values);

		// Total Zones & Growth Rate

		$report_data = $final_report_data;

		$dates = array_keys($report_data['Total']);
		// $dates = formatDateVariable($dates, $request->start_date, $request->end_date);
		$type_name = 'Countries';
		return view('client_view.reports.sales_gathering_analysis.sales_discounts_analysis_report', compact('company', 'view_name', 'zones_names', 'dates', 'report_data', 'type_name'));
	}
	public function CountriesSalesAnalysisResult(Request $request, Company $company)
	{
		$dimension = $request->report_type;

		$report_data = [];
		$growth_rate_data = [];
		$countries = is_array(json_decode(($request->countries[0]))) ? json_decode(($request->countries[0])) : $request->countries;

		foreach ($countries as  $main_row) {


			$countries_data = collect(DB::select(DB::raw(
				"
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,country
                FROM sales_gathering
                WHERE ( company_id = '" . $company->id . "'AND country = '" . $main_row . "' AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
                ORDER BY id "
			)))->groupBy('gr_date')->map(function ($item) {
				return $item->sum('net_sales_value');
			})->toArray();

			$interval_data_per_item = [];
			$years = [];
			if (count($countries_data) > 0) {
				array_walk($countries_data, function ($val, $date) use (&$years) {
					$years[] = date('Y', strtotime($date));
				});
				$years = array_unique($years);
				$report_data[$main_row] = $countries_data;
				$interval_data_per_item[$main_row] = $countries_data;
				$interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$interval_data_per_item, $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);

				$report_data[$main_row] = $interval_data['data_intervals'][$request->interval][$main_row] ?? [];
				$growth_rate_data[$main_row] = $this->growthRate($report_data[$main_row]);
			}
		}

		$total_countries = $this->finalTotal($report_data);
		$total_countries_growth_rates =  $this->growthRate($total_countries);
		$final_report_data = [];
		$countries_names = [];
		foreach ($countries as  $main_row) {
			$final_report_data[$main_row]['Sales Values'] = ($report_data[$main_row] ?? []);
			$final_report_data[$main_row]['Growth Rate %'] = ($growth_rate_data[$main_row] ?? []);
			$countries_names[] = (str_replace(' ', '_', $main_row));
		}
		
		$dates = array_keys( $total_countries ?? []); 
		$final_report_data = HArr::getKeysSortedDescByKey($final_report_data,'Sales Values');

		return view('client_view.reports.sales_gathering_analysis.countries_sales_report', compact('company', 'countries_names', 'total_countries_growth_rates', 'final_report_data', 'total_countries','dates'));
	}
	public function growthRate($data)
	{

		$prev_month = 0;
		$final_data = [];
		foreach ($data as $date => $value) {
			$prev_month = (round($prev_month));
			if ($prev_month <= 0 && $value <= 0) {
				$final_data[$date] = 0;
			}
			if ($prev_month <  0 && $value >= 0) {
				$final_data[$date] =  ((($value - $prev_month) / $prev_month) * 100) * (-1);
			} else {

				$final_data[$date] = $prev_month != 0 ? (($value - $prev_month) / $prev_month) * 100 : 0;
			}
			$prev_month = $value;
		}
		return $final_data;
	}
	// Ajax


}
