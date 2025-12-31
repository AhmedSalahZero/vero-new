<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZoneAgainstAnalysisReport
{
	use GeneralFunctions;
	public function index(Company $company)
	{
		if (request()->route()->named('zone.salesChannels.analysis')) {
			$type = 'sales_channel';
			$view_name = 'Zones Against Sales Channels Trend Analysis';
		} elseif (request()->route()->named('zone.customers.analysis')) {
			$type = 'customer_name';
			$view_name = 'Zones Against Customers Trend Analysis';
		} elseif (request()->route()->named('zone.categories.analysis')) {
			$type  = 'category';
			$view_name = 'Zones Against Categories Trend Analysis';
		} elseif (request()->route()->named('zone.products.analysis')) {
			$type  = 'product_or_service';
			$view_name = 'Zones Against Products / Services Trend Analysis';
		} elseif (request()->route()->named('zone.principles.analysis')) {
			$type  = 'principle';
			$view_name = 'Zones Against Principles Trend Analysis';
		} elseif (request()->route()->named('zone.Items.analysis')) {
			$type  = 'product_item';
			$view_name = 'Zones Against Products Items Trend Analysis';
		} elseif (request()->route()->named('zone.salesPersons.analysis')) {
			$type  = 'sales_person';
			$view_name = 'Zones Against Sales Persons Trend Analysis';
		} elseif (request()->route()->named('zone.salesDiscount.analysis')) {
			$type  = 'quantity_discount';
			$view_name = 'Zones Against Sales Discount Trend Analysis';
		} elseif (request()->route()->named('zone.businessSectors.analysis')) {
			$type  = 'business_sector';
			$view_name = 'Zones Against Business Sectors Trend Analysis';
		}elseif (request()->route()->named('zone.businessUnits.analysis')) {
			$type  = 'business_unit';
			$view_name = 'Zones Against Business Units Trend Analysis';
		}
		elseif (request()->route()->named('zone.businessUnits.analysis')) {
			$type  = 'business_unit';
			$view_name = 'Zones Against Business Units Trend Analysis';
		} 
		elseif (request()->route()->named('zone.branches.analysis')) {
			$type  = 'branch';
			$view_name = 'Zones Against Branches Trend Analysis';
		}
		elseif (request()->route()->named('zone.countries.analysis')) {
			$type  = 'country';
			$view_name = 'Zones Against Countries Trend Analysis';
		}
		 elseif (request()->route()->named('zone.products.averagePrices')) {
			$type  = 'averagePrices';
			$view_name = 'Zones Products / Services Average Prices';
		} elseif (request()->route()->named('branch.products.averagePrices')) {
			$type  = 'averagePrices';
			$view_name = 'Branches Products / Services Average Prices';
		} elseif (request()->route()->named('zone.Items.averagePrices')) {
			$type  = 'averagePricesProductItems';
			$view_name = 'Zones Products Items Average Prices';
		}

		$name_of_selector_label = str_replace(['Zones Against ', ' Trend Analysis'], '', $view_name);

		if ($type == 'averagePrices') {
			$name_of_selector_label = 'Products / Services';
		} elseif ($type  == 'averagePricesProductItems') {
			$name_of_selector_label = 'Products Items';
		}
		return view('client_view.reports.sales_gathering_analysis.zone_analysis_form', compact('company', 'name_of_selector_label', 'type', 'view_name'));
	}
	public function ZoneSalesAnalysisIndex(Company $company)
	{

		// Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
		// $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
		$zones =  getTypeFor('zone', $company->id);

		// $zones =  SalesGathering::company()->whereNotNull('zone')->where('zone','!=','')->groupBy('zone')->selectRaw('zone')->get()->pluck('zone')->toArray();

		return view('client_view.reports.sales_gathering_analysis.zone_sales_form', compact('company', 'zones'));
	}

	public function result(Request $request, Company $company, $result = 'view', $secondReport = true)
	{
		if($request->report_type =='comparing' && $secondReport == true ){
			$firstReportStartDate = $request->get('start_date_second');
			$firstReportEndDate = $request->get('end_date_second');
			$startDate = $request->get('start_date');
			$endDate = $request->get('end_date');
			if(Carbon::make($firstReportEndDate)->lessThan(Carbon::make($endDate))){
				$request->merge([
					'start_date'=>$firstReportStartDate,
					'end_date'=>$firstReportEndDate,
					'start_date_second'=>$startDate,
					'end_date_second'=>$endDate
				]);
				
			}
		}
		
		$report_data = [];
		$report_data_quantity = [];
		$growth_rate_data = [];
		$zones_names = [];
		$final_report_total = [];
		$data_type = ($request->data_type === null || $request->data_type == 'value') ? 'net_sales_value' : 'quantity';
		$zones = is_array(json_decode(($request->zones[0]))) ? json_decode(($request->zones[0])) : $request->zones;
		$type = $request->type;
		$view_name = $request->view_name;
		$name_of_report_item  = ($result == 'view') ? 'Sales Values' : 'Avg. Prices';
		foreach ($zones as  $zone) {
			if ($result == 'view') {
				
				$zones_data = collect(DB::select(DB::raw(
					"
                        SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , " . $data_type . " ,zone," . $type . "
                        FROM sales_gathering
                        WHERE ( company_id = '" . $company->id . "'AND zone = '" . $zone . "' AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
                        ORDER BY id"
				)))->groupBy($type)->map(function ($item) use ($data_type) {
					return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {

						return $sub_item->sum($data_type);
					});
				})->toArray();
			} else {
				$zones_data = DB::table('sales_gathering')
					->where('company_id', $company->id)
					->where('zone', $zone)
					->whereNotNull($type)
					->whereBetween('date', [$request->start_date, $request->end_date])
					->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,
                    (IFNULL(' . $data_type . ',0) ) as ' . $data_type . ' ,zone,' . $type)
					->get()
					->groupBy($type)->map(function ($item) use ($data_type) {
						return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {
							return $sub_item->sum($data_type);
						});
					})->toArray();


				$zones_data_quantity = DB::table('sales_gathering')
					->where('company_id', $company->id)
					->where('zone', $zone)
					->whereNotNull($type)
					->whereBetween('date', [$request->start_date, $request->end_date])
					->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,
                    (IFNULL(' . $data_type . ',0) ) as ' . $data_type . ' , IFNULL(quantity_bonus,0) quantity_bonus , IFNULL(quantity,0) quantity,zone,' . $type)
					->get()
					->groupBy($type)->map(function ($item) use ($data_type) {
						return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {
							return ($sub_item->sum('quantity_bonus') + $sub_item->sum('quantity'));
						});
					})->toArray();
			}

			foreach ($request->sales_channels as $sales_channel_key => $sales_channel) {

				$data_per_main_item = $zones_data[$sales_channel] ?? [];

				$years = [];
				if (count(($data_per_main_item)) > 0) {


					// Data & Growth Rate Per Sales Channel
					array_walk($data_per_main_item, function ($val, $date) use (&$years) {
						$years[] = date('Y', strtotime($date));
					});
					$years = array_unique($years);

					$report_data[$zone][$sales_channel][$name_of_report_item] = $data_per_main_item;
					$interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data[$zone][$sales_channel], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
					$report_data[$zone][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];

					$report_data[$zone]['Total']  = $this->finalTotal([($report_data[$zone]['Total']  ?? []), ($report_data[$zone][$sales_channel][$name_of_report_item] ?? [])]);
					$report_data[$zone][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data[$zone][$sales_channel][$name_of_report_item] ?? []));
				}
			}

			if ($result == 'array') {


				foreach ($request->sales_channels as $sales_channel_key => $sales_channel) {

					$data_per_main_item = $zones_data_quantity[$sales_channel] ?? [];

					$years = [];
					if (count(($data_per_main_item)) > 0) {


						// Data & Growth Rate Per Sales Channel
						array_walk($data_per_main_item, function ($val, $date) use (&$years) {
							$years[] = date('Y', strtotime($date));
						});
						$years = array_unique($years);

						$report_data_quantity[$zone][$sales_channel][$name_of_report_item] = $data_per_main_item;
						$interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data_quantity[$zone][$sales_channel], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
						$report_data_quantity[$zone][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];

						$report_data_quantity[$zone]['Total']  = $this->finalTotal([($report_data_quantity[$zone]['Total']  ?? []), ($report_data_quantity[$zone][$sales_channel][$name_of_report_item] ?? [])]);
						$report_data_quantity[$zone][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data_quantity[$zone][$sales_channel][$name_of_report_item] ?? []));
					}
				} {
				}


				foreach ($report_data as $reportType => $dates) {
					// Baby 20
					if ($reportType == $zone) {
						foreach ($dates as $dateName => $items) {
							if ($dateName != 'Total') {
								//Avg. Prices
								foreach ($items as $itemKey => $values) {
									if ($itemKey == 'Avg. Prices') {
										foreach ($values as $datee => $dateVal) {

											$report_data[$reportType][$dateName][$itemKey][$datee] =
												$report_data_quantity[$reportType][$dateName][$itemKey][$datee]   ?
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

			$final_report_total = $this->finalTotal([($report_data[$zone]['Total'] ?? []), ($final_report_total ?? [])]);
			$report_data[$zone]['Growth Rate %'] =  $this->growthRate(($report_data[$zone]['Total'] ?? []));
			$zones_names[] = (str_replace(' ', '_', $zone));
		}

		foreach ($report_data as $r => $d) {
			unset($report_data[$r]['Totals']);
		}


		// Total Zones & Growth Rate

		$report_data['Total'] = $final_report_total;
		$report_data['Growth Rate %'] =  $this->growthRate($report_data['Total']);
		$dates = array_keys($report_data['Total']);
		// $dates = formatDateVariable($dates, $request->start_date, $request->end_date);
		$Items_names = $zones_names;
		$report_view = getComparingReportForAnalysis($request, $report_data, $secondReport, $company, $dates, $view_name, $Items_names, 'zone');

		if ($report_view instanceof View) {
			return $report_view;
		}

		if ($request->report_type == 'comparing') {
			return [
				'report_data' => $report_data,
				'dates' => $dates,
				'full_date' => Carbon::make($request->start_date)->format('d M Y') . ' ' . __('To') . ' ' . Carbon::make($request->end_date)->format('d M Y')
			];
		}


		if ($result == 'view') {
			return view('client_view.reports.sales_gathering_analysis.zone_analysis_report', compact('company', 'name_of_report_item', 'view_name', 'zones_names', 'dates', 'report_data',));
		} else {
			return ['report_data' => $report_data, 'view_name' => $view_name, 'names' => $zones_names];
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
		$zones = is_array(json_decode(($request->zones[0]))) ? json_decode(($request->zones[0])) : $request->zones;
		$type = $request->type;
		$view_name = $request->view_name;
		$fields = '';
		foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
			$fields .= $sales_discount_field . ',';
		}

		$zones_discount = [];
		foreach ($zones as  $zone) {
			$sales = collect(DB::select(DB::raw(
				"
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields . " zone
                FROM sales_gathering
                WHERE ( company_id = '" . $company->id . "'AND zone = '" . $zone . "' AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
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
		// Intervals For Sales Values


		$sales_values = $this->finalTotal([$sales_values ?? []]);

		$total = $final_report_data['Total'];
		unset($final_report_data['Total']);
		$final_report_data['Total'] = $total;
		$final_report_data['Discount % / Total Sales'] = $this->operationAmongTwoArrays($final_report_data['Total'], $sales_values);

		// Total Zones & Growth Rate

		$report_data = $final_report_data;

		$dates = array_keys($report_data['Total']);

		// $dates = formatDateVariable($dates, $request->start_date, $request->end_date);

		$type_name = 'Zones';

		return view('client_view.reports.sales_gathering_analysis.sales_discounts_analysis_report', compact('company', 'view_name', 'zones_names', 'dates', 'report_data', 'type_name'));
	}

	public function ZoneSalesAnalysisResult(Request $request, Company $company)
	{
		$dimension = $request->report_type;

		$report_data = [];
		$growth_rate_data = [];
		$zones = is_array(json_decode(($request->zones[0]))) ? json_decode(($request->zones[0])) : $request->zones;


		foreach ($zones as  $zone) {

			$zones_data = collect(DB::select(DB::raw(
				"
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,zone
                FROM sales_gathering
                WHERE ( company_id = '" . $company->id . "'AND zone = '" . $zone . "' AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
                ORDER BY id "
			)))->groupBy('gr_date')->map(function ($item) {
				return $item->sum('net_sales_value');
			})->toArray();
			// $zones_per_month = [];

			$interval_data_per_item = [];
			$years = [];
			if (count($zones_data) > 0) {


				// Data & Growth Rate Per Sales Channel
				array_walk($zones_data, function ($val, $date) use (&$years) {
					$years[] = date('Y', strtotime($date));
				});
				$years = array_unique($years);
				$report_data[$zone] = $zones_data;
				$interval_data_per_item[$zone] = $zones_data;
				$interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$interval_data_per_item, $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);

				$report_data[$zone] = $interval_data['data_intervals'][$request->interval][$zone] ?? [];
				$growth_rate_data[$zone] = $this->growthRate($report_data[$zone]);
			}
		}
		// Intervals::intervalsWithSubArrays($final_report_data, $financial);

		$total_zones = $this->finalTotal($report_data);
		$total_zones_growth_rates =  $this->growthRate($total_zones);
		$final_report_data = [];
		$zones_names = [];
		foreach ($zones as  $zone) {
			$final_report_data[$zone]['Sales Values'] = ($report_data[$zone] ?? []);
			$final_report_data[$zone]['Growth Rate %'] = ($growth_rate_data[$zone] ?? []);
			$zones_names[] = (str_replace(' ', '_', $zone));
		}
		
		$dates = array_keys( $total_zones ?? []); 
		$final_report_data = HArr::getKeysSortedDescByKey($final_report_data,'Sales Values');
		return view('client_view.reports.sales_gathering_analysis.zone_sales_report', compact('company', 'zones_names', 'total_zones_growth_rates', 'final_report_data', 'total_zones','dates'));
	}
	public function growthRate($data)
	{

		$prev_month = 0;
		$final_data = [];
		foreach ((array)$data as $date => $value) {
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
	public function ZonesData(Request $request, Company $company)
	{

		$start_date = $request->get('start_date');
		$end_date = $request->get('end_date');
		
		if (false !== $found = array_search('all', (array)$request->main_data)) {
			$orderByRaw = $request->field == 'day_name'  ? "ORDER BY FIELD(`day_name`, 'Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday')" :'sum(net_sales_value) desc';
			$selectRow = (($request->field) . (' , net_sales_value '));
			$data = SalesGathering::company()
				->orderByRaw($orderByRaw)
				->whereNotNull($request->field)
				->when($start_date && !$end_date, function (Builder $builder) use ($start_date) {
					$builder->where('date', '>=', $start_date);
				})
				->when($end_date && !$start_date, function (Builder $builder) use ($end_date) {
					$builder->where('date', '<=', $end_date);
				})->when($end_date &&  $start_date, function (Builder $builder) use ($start_date, $end_date) {
					$builder->whereBetween('date', [$start_date, $end_date]);
				})
				->groupBy($request->field)
				->selectRaw($selectRow)
				->where(function ($query) use ($request) {
					if (($request->second_main_data) !== null) {
						$query->whereNotNull($request->sub_main_field)
							->whereIn($request->sub_main_field, (is_array($request->second_main_data)) ? ($request->second_main_data ?? []) : [$request->second_main_data]);
					}
					if (($request->third_main_data) !== null) {
						$query->whereNotNull($request->third_main_field)
						->whereIn($request->third_main_field, (is_array($request->third_main_data)) ? ($request->third_main_data ?? []) : [$request->third_main_data]);
					}
				})
				->get()
				->pluck($request->field)
				->toArray();
			
		} else {
			$orderByRaw = $request->field == 'day_name'  ? "FIELD(`day_name`, 'Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday')" :'sum(net_sales_value) desc';
			$selectRow = (($request->field ?: 'product_item ') . (' , sum(net_sales_value) '));
			$data = SalesGathering::company()
				->when($start_date && !$end_date, function (Builder $builder) use ($start_date) {
					$builder->where('date', '>=', $start_date);
				})
				->when($end_date && !$start_date, function (Builder $builder) use ($end_date) {
					$builder->where('date', '<=', $end_date);
				})->when($end_date &&  $start_date, function (Builder $builder) use ($start_date, $end_date) {
					$builder->whereBetween('date', [$start_date, $end_date]);
				})
				->whereNotNull($request->main_field)
				->whereIn($request->main_field, ($request->main_data ?? []))
				->whereNotNull($request->field ?: 'product_item')
				->groupBy($request->field ?: 'product_item')
				->selectRaw($selectRow)
				->orderByRaw($orderByRaw)
				->where(function ($query) use ($request) {
					if (($request->second_main_data) !== null) {
						$query->whereNotNull($request->sub_main_field)->whereIn($request->sub_main_field, (is_array($request->second_main_data)) ? ($request->second_main_data ?? []) : [$request->second_main_data]);
					}
					if (($request->third_main_data) !== null) {
						$query->whereNotNull($request->third_main_field)->whereIn($request->third_main_field, (is_array($request->third_main_data)) ? ($request->third_main_data ?? []) : [$request->third_main_data]);
					}
				})

				->get()
				->pluck($request->field ?: 'product_item')

				->toArray();
		}
		return $data;
	}
	public function dataView(Request $request, Company $company)
	{
		$name = $request->name;

		$data = SalesGathering::company()
			->whereNotNull($request->main_field)
			->whereIn($request->main_field, ($request->main_data ?? []))
			->whereNotNull($request->field)
			->groupBy($request->field)
			->selectRaw($request->field)
			->where(function ($query) use ($request) {
				if (($request->second_main_data) !== null) {
					$query->whereNotNull($request->sub_main_field)->whereIn($request->sub_main_field, ($request->second_main_data ?? []));
				}
				if (($request->third_main_data) !== null) {
					$query->whereNotNull($request->third_main_field)->whereIn($request->third_main_field, ($request->third_main_data ?? []));
				}
			})
			->get()
			->pluck($request->field)
			->toArray();


		return view('ajax_views.multi_selections_view', compact('data', 'name', 'company'));
	}
}
  
