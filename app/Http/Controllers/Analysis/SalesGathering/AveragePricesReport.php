<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Models\Company;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;

class AveragePricesReport
{
	use GeneralFunctions;
	public function result(Request $request, Company $company)
	{
		$full_report_data = [];
		$view_name = '';
		$names = [];

		if ($request->type_of_report == "categories_products_avg") {
			$full_report_data = (new CategoriesAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Categories / Products';
		} elseif ($request->type_of_report == "zones_products_avg") {
			$full_report_data = (new ZoneAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Zones / Products';
		} elseif ($request->type_of_report == "salesChannels_products_avg") {
			$full_report_data = (new SalesChannelsAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Sales Channels / Products';
		} elseif ($request->type_of_report == "businessSectors_products_avg") {
			$full_report_data = (new BusinessSectorsAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Business Sectors / Products';
		}elseif ($request->type_of_report == "businessUnits_products_avg") {
			$full_report_data = (new BusinessUnitsAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Business Units / Products';
		} elseif ($request->type_of_report == "products_Items_avg") {
			$full_report_data = (new ProductsAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Product Items';
		} elseif ($request->type_of_report == "zones_Items_avg") {
			$full_report_data = (new ZoneAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Zones / Products Items';
		} elseif ($request->type_of_report == "salesChannels_Items_avg") {
			$full_report_data = (new SalesChannelsAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Sales Channels / Products Items';
		} elseif ($request->type_of_report == "businessSectors_Items_avg") {
			$full_report_data = (new BusinessSectorsAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Business Sectors / Products Items';
		}elseif ($request->type_of_report == "businessUnits_Items_avg") {
			$full_report_data = (new BusinessUnitsAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Business Units / Products Items';
		} elseif ($request->type_of_report == "countries_products_avg") {
			$full_report_data = (new CountriesAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Countries / Products';
		} elseif ($request->type_of_report == "countries_Items_avg") {
			$full_report_data = (new CountriesAgainstAnalysisReport)->result($request, $company, 'array');
			$type = 'Countries / Products Items';
		}
		$report_data = [];
		$report_data_interval = $full_report_data['report_data'] ?? [];

		if (count($report_data_interval) > 0) {
			$interval_number = $this->IntervalNumber($request->interval);

			foreach ($report_data_interval as $main_item => $main_item_data) {

				foreach ($main_item_data as $item => $item_data) {


					if ($item !== 'Total' && $item != 'Growth Rate %') {

						$avg_prices = $item_data["Avg. Prices"] ?? [];
						$data = $this->operationAmongArrayAndNumber($avg_prices, 1);

						$report_data[$main_item][$item]["Avg. Prices"] = $data;


						$report_data[$main_item][$item]["Growth Rate %"] =  $item_data["Growth Rate %"] ?? [];
					} else {
						$report_data[$main_item][$item] = [];
					}
				}
			}

			$report_data['Total'] = [];
			$report_data['Growth Rate %'] = [];
		}
		foreach ($report_data as $r => $d) {
			unset($report_data[$r]['Totals']);
		}

		$view_name = $full_report_data['view_name'] ?? '';
		$names = $full_report_data['names'] ?? [];
		$dates = array_keys(($report_data_interval['Total'] ?? []));
		return view('client_view.reports.sales_gathering_analysis.average_prices.average_prices_report', compact('company', 'type', 'view_name', 'names', 'dates', 'report_data'));
	}
}
