<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Analysis\SalesGathering\ProductsAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\SalesBreakdownDataWithQuantity;
use App\Http\Controllers\Analysis\SalesGathering\salesReport;
use App\Models\Company;
use App\Models\CustomizedFieldsExportation;
use App\Models\Log;
use App\Models\QuantityCategory;
use App\Models\QuantityModifiedSeasonality;
use App\Models\QuantityModifiedTarget;
use App\Models\QuantityNewProductAllocationBase;
use App\Models\QuantityProduct;
use App\Models\QuantityProductSeasonality;
use App\Models\QuantitySalesForecast;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class QuantitySalesForecastReport
{
	use GeneralFunctions;
	public function fields($company)
	{
		$fields = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');

		return (false !== $found = array_search('Product Item', $fields));
	}
	public function result(Company $company, Request $request)
	{


		$sales_forecast = QuantitySalesForecast::company()->first();

		$has_product_item = $this->fields($company);

		if ($sales_forecast === null) {
			$start_date = now()->startOfYear()->format('Y-m-d'); // 01-01-2023
			$end_date = now()->endOfYear()->format('Y-m-d'); // 31/12/2023

			if ($request->isMethod('GET')) {
				Log::storeNewLogRecord('enterSection',null,__('Sales Forecast Quantity Base'));
				
				$request['start_date'] = $start_date;
				$request['end_date'] = $end_date;
			} elseif ($request->isMethod('POST')) {

				$start_date = $request['start_date'];
				$end_date = $request['end_date'];
			}

			$request['report_type'] = 'comparing';
			$start_year = date('Y', strtotime($start_date));
			$date_of_previous_3_years = ($start_year - 3) . '-01-01';
			$request['start_date'] = $date_of_previous_3_years;
			$end_date_for_report = ($start_year - 1) . '-01-01';
			$request['end_date'] = $start_date;
			$salesReport = (new salesReport)->result($request, $company, 'array');
		
			if(!count($salesReport['total_full_data'])){
				return redirect()->route('salesGathering.index',[
					'company'=>$company->id
				])->with('fail',__('Please at least upload pervious Year Sales Data'));
			}
			$request['type'] = 'product_item';

			$product_item_breakdown_data_previous_3_year = (new SalesBreakdownDataWithQuantity)->salesBreakdownAnalysisResult($request, $company, 'array', 3);
			$request['start_date'] = $this->dateCalc($start_date, -12, 'Y-m-d');
			$product_item_breakdown_data_previous_1_year = (new SalesBreakdownDataWithQuantity)->salesBreakdownAnalysisResult($request, $company, 'array');


			// Pervious Year Sales
			$previous_1_year_sales = array_sum($salesReport['report_data'][$start_year - 1]['Sales Values'] ?? []);
			$previous_2_years_sales = array_sum($salesReport['report_data'][$start_year - 2]['Sales Values'] ?? []);
			$previous_3_years_sales = array_sum($salesReport['report_data'][$start_year - 3]['Sales Values'] ?? []);
			// Year  Gr Rate
			$previous_year_gr = $previous_2_years_sales == 0 ? 0 : ($previous_1_year_sales - $previous_2_years_sales) / $previous_2_years_sales * 100;
			// Average Last 3 Years
			$pervious_years_sales = [
				$start_year - 1 => $previous_1_year_sales,
				$start_year - 2 => $previous_2_years_sales,
				$start_year - 3 => $previous_3_years_sales,
				$start_year - 4 => 0,
			];
			$pervious_years_sales = array_filter($pervious_years_sales);
			// Average Last 3 Years
			$average_last_3_years = count($pervious_years_sales)  == 0 ? 0 : array_sum($pervious_years_sales) / count($pervious_years_sales);
			// Previous year
			$previous_year = $start_year - 1;
			// Previous Year Seasonality
			$previous_year_seasonality = $salesReport['report_data'][$start_year - 1]['Month Sales %'] ?? [];
			$previous_year_seasonality = $this->sorting($previous_year_seasonality);
			
			// Last 3 Years Seasonality
			$last_3_years_seasonality = $salesReport['total_full_data'] ?? [];
			$last_3_years_seasonality = $this->sorting($last_3_years_seasonality);


			$end_date = $this->dateCalc($start_date, 11, 'Y-m-t');

			// // Creating Array For View
			$sales_forecast["start_date"] = $start_date;
			$sales_forecast["end_date"] =   $end_date;
			$sales_forecast["previous_year"] = $previous_year;
			$sales_forecast["previous_1_year_sales"] = $previous_1_year_sales;
			$sales_forecast["previous_year_gr"] = $previous_year_gr;
			$sales_forecast["average_last_3_years"] = $average_last_3_years;
			$sales_forecast["previous_year_seasonality"] = $product_item_breakdown_data_previous_1_year;
			$sales_forecast["last_3_years_seasonality"] = $product_item_breakdown_data_previous_3_year;
		} else {
			$start_date = $sales_forecast->start_date;
		}
		$start_year = date('Y', strtotime($start_date));
		$previousYearSeasonality = $sales_forecast["previous_year_seasonality"] ?? [] ;
		
		$top_previous_year_100 = array_column($previousYearSeasonality, 'item');
		// others_products_previous_year
		if (count(($sales_forecast['others_products_previous_year'] ?? [])) > 0) {

			foreach ($sales_forecast['others_products_previous_year'] as $key => $value) {
				if (false !== $found = array_search($value, $top_previous_year_100)) {
					unset($top_previous_year_100[$found]);
				}
			}
		}

		$selector_products_previous_year = SalesGathering::company()
			->whereNotNull('product_item')
			->whereBetween('date', [($sales_forecast['previous_year'] . '-01-01'), $sales_forecast['previous_year'] . '-12-31'])
			->where('product_item', '!=', '')
			->whereNotIn('product_item', $top_previous_year_100)
			->groupBy('product_item')
			->pluck('product_item')
			->toArray();


		$top_previous_3_years_100 = array_column($sales_forecast["last_3_years_seasonality"], 'item');
		$date_of_previous_3_years = ($start_year - 3);
		// others_products_previous_3_year
		if (count(($sales_forecast['others_products_previous_3_year'] ?? [])) > 0) {

			foreach ($sales_forecast['others_products_previous_3_year'] as $key => $value) {
				if (false !== $found = array_search($value, $top_previous_3_years_100)) {
					unset($top_previous_3_years_100[$found]);
				}
			}
		}

		$selector_products_previous_3_year = SalesGathering::company()
			->whereNotNull('product_item')
			->whereBetween('date', [($date_of_previous_3_years . '-01-01'), $start_year . '-12-31'])
			->where('product_item', '!=', '')
			->whereNotIn('product_item', $top_previous_3_years_100)
			->groupBy('product_item')
			->pluck('product_item')
			->toArray();

		$dates = [];
		$quarter_dates = [];
		$counter = 1;
		for ($month = 0; $month < 12; $month++) {
			$date = $this->dateCalc($start_date, $month);
			$dates[$date] = '';
			if ($counter % 3 == 0) {
				$quarter_dates[$date] = '';
			}
			$counter++;
		}

		$sales_forecast["quarter_dates"] = $quarter_dates;
		$sales_forecast["dates"] = $dates;
		// $selector_products = SalesGathering::company()
		// ->whereNotNull('product_item')
		// ->where('product_item', '!=', '')
		// ->whereNotIn('product_item', $products_used)
		// ->groupBy('product_item')
		// ->pluck('product_item')
		// ->toArray();

		// $sales_forecast['previous_year_seasonality'] = $this->sorting($sales_forecast['previous_year_seasonality']);
		// $sales_forecast['last_3_years_seasonality'] = $this->sorting($sales_forecast['last_3_years_seasonality']);
		return view(
			'client_view.quantity_forecast.sales_forecast',
			compact('company', 'sales_forecast', 'has_product_item', 'selector_products_previous_year', 'selector_products_previous_3_year')
		);
	}

	public function save(Company $company, Request $request, $noReturn = false)
	{
		if ($request->submit == 'Show Result') {

			$request['start_date']  = $request->previous_year . '-01-01';
			$request['end_date']    = $request->previous_year . '-12-31';
			$request['type'] = 'product_item';
			$product_item_breakdown_data_previous_year = (new SalesBreakdownDataWithQuantity)->salesBreakdownAnalysisResult($request, $company, 'withOthers');
			$request['previous_year_seasonality'] = $this->addingOthersToData($product_item_breakdown_data_previous_year, $request->others_products_previous_year);

			$request['start_date']  = ($request->previous_year - 2) . '-01-01';
			$request['end_date']    = $request->previous_year . '-12-31';
			$request['type'] = 'product_item';
			$product_item_breakdown_data_previous_3_years = (new SalesBreakdownDataWithQuantity)->salesBreakdownAnalysisResult($request, $company, 'withOthers', 3);
			$request['last_3_years_seasonality'] = $this->addingOthersToData($product_item_breakdown_data_previous_3_years, $request->others_products_previous_3_year);
			$end_date = $this->dateCalc($request['start_date'], 11, 'Y-m-t');

			QuantitySalesForecast::updateOrCreate(
				['company_id' => $company->id],
				[
					'start_date' => $request['start_date'],
					'end_date' => $end_date,
					'previous_year' => $request['previous_year'],
					'previous_1_year_sales' => $request['previous_1_year_sales'],
					'previous_year_gr' => $request['previous_year_gr'],
					'others_products_previous_year' => $request['others_products_previous_year'],
					'others_products_previous_3_year' => $request['others_products_previous_3_year'],
					'previous_year_seasonality' => $request['previous_year_seasonality'],
					'average_last_3_years' => $request['average_last_3_years'],
					'last_3_years_seasonality' => $request['last_3_years_seasonality']
				]

			);
		}
		if (isset($request['summary_report'])) {
			return (new QuantitySummaryController())->goToSummaryReport($request, $company);
		}

		$sales_forecast = QuantitySalesForecast::company()->first();

		// if(forecastHasBeenChanged($sales_forecast  , $request->all()))
		// {
		//        deleteProductItemsForForecast($company->id);
		//        deleteNewProductAllocationBaseForForecast($company->id);
		// }
		if ($sales_forecast !== null && $request['start_date'] !== $sales_forecast->start_date) {

			$sales_forecast->delete();
			// toastr()->success('Please refill the data');
			// Session::flash('message', 'IT WORKS!');

			return $this->result($company, $request);
		}

		$request->validate(
			[
				'start_date' => 'required',
				'target_base' => 'required',
				'quantity_growth_rate' => $request['target_base'] == 'previous_year' || $request['target_base'] == 'previous_3_years' ? 'required|numeric|min:0' : '',
				'prices_increase_rate' => ($request['target_base'] == 'previous_year' || $request['target_base'] == 'previous_3_years') ? 'required|numeric|min:0' : '',
				'other_products_growth_rate' => ($request['target_base'] == 'previous_year' || $request['target_base'] == 'previous_3_years') ? 'required|numeric|min:0' : '',
				// 'new_start' => $request['target_base'] == 'new_start' ? 'required' : '',
				'seasonality' =>   'sometimes|required',
				'number_of_products' => $request['add_new_products'] == 1 ? 'required|numeric|min:1' : '',

			]
			// [
			//     'prices_increase_rate.min' => 'The sales target must be greater than zero'
			// ]
		);
		$sales_forecast = $sales_forecast !== null ? $sales_forecast : new QuantitySalesForecast;
		$end_date = $this->dateCalc($request['start_date'], 11, 'Y-m-t');
		$sales_forecast->company_id = $company->id;
		$sales_forecast->start_date = $request['start_date'];
		$sales_forecast->end_date = $end_date;
		$sales_forecast->previous_year = $request['previous_year'];
		$sales_forecast->previous_1_year_sales = $request['previous_1_year_sales'];
		$sales_forecast->previous_year_gr = $request['previous_year_gr'];
		$sales_forecast->average_last_3_years = $request['average_last_3_years'];
		$sales_forecast->previous_year_seasonality = $request['previous_year_seasonality'];
		$sales_forecast->last_3_years_seasonality = $request['last_3_years_seasonality'];

		$sales_forecast->target_base = $request['target_base'];
		if ($request['target_base'] == 'previous_year' || $request['target_base'] == 'previous_3_years') {
			$sales_forecast->quantity_growth_rate = $request['quantity_growth_rate'];
			$sales_forecast->prices_increase_rate = $request['prices_increase_rate'];
			$sales_forecast->other_products_growth_rate = $request['other_products_growth_rate'];
		} elseif ($request['target_base'] == 'new_start') {
			$sales_forecast->quantity_growth_rate = null;
			$sales_forecast->prices_increase_rate = null;
			$sales_forecast->other_products_growth_rate = null;
		}
		$sales_forecast->add_new_products = $request['add_new_products'] ?? 0;
		$sales_forecast->number_of_products = $request['add_new_products'] == 1 ?  $request['number_of_products'] : 0;
		$sales_forecast->seasonality = $request['seasonality'];

		if ($request['seasonality'] == 'new_seasonality_monthly') {
			$sales_forecast->new_seasonality = $request['new_seasonality_monthly'];
		} elseif ($request['seasonality'] == 'new_seasonality_quarterly') {
			$sales_forecast->new_seasonality = $request['new_seasonality_quarterly'];
		}

		$sales_forecast->save();
		if ($noReturn) {
			return;
		}
		toastr()->success('Saved Successfully');

		// if ($request['add_new_products'] == 0) {
		//     return redirect()->route('products.sales.targets.quantity', $company);
		// } else {
		//     return redirect()->route('categories.quantity.create', $company);
		// }
		return redirect()->route('forecasted.sales.values', $company);
	}
	public function forecastedSalesValues(Company $company, Request $request)
	{
		$sales_forecast = QuantitySalesForecast::company()->first();
		$forecasted_sales_date = [];
		$quantity_growth_rate = $sales_forecast->quantity_growth_rate;
		$prices_increase_rate = $sales_forecast->prices_increase_rate;
		$other_products_growth_rate = $sales_forecast->other_products_growth_rate;
		if ($sales_forecast->target_base == 'previous_year') {


			$forecasted_sales_date = collect($sales_forecast->previous_year_seasonality)->flatMap(function ($data, $key) use ($quantity_growth_rate, $prices_increase_rate, $other_products_growth_rate) {
				$forecasted_quantity = (1 + (($quantity_growth_rate ?? 0) / 100)) * ($data['Sales Quantity']);
				$forecasted_price = (1 + (($prices_increase_rate ?? 0) / 100)) * ($data['Average Price']);
				if (str_contains($data['item'], 'Others') === false) {
					return [
						[
							'item' => $data['item'],
							'Sales %' => $data['Sales %'],
							'Sales Value' => $data['Sales Value'],
							'Average Price' => $data['Average Price'],
							'Sales Quantity' => $data['Sales Quantity'],
							'Forecasted Quantity' => $forecasted_quantity,
							'Forecasted Price' => $forecasted_price,
							'Forecasted Sales Value' => $forecasted_quantity * $forecasted_price
						]
					];
				} elseif (str_contains($data['item'], 'Others') === true) {

					return [
						[
							'item' => $data['item'],
							'Sales %' => $data['Sales %'],
							'Sales Value' => $data['Sales Value'],
							'Average Price' => $data['Average Price'],
							'Sales Quantity' => $data['Sales Quantity'],
							'Forecasted Quantity' => 0,
							'Forecasted Price' => 0,
							'Forecasted Sales Value' => (1 + (($other_products_growth_rate ?? 0) / 100)) * ($data['Sales Value'])
						]
					];
				}
			})->toArray();
			// $sales_forecast->previous_year_seasonality = $forecasted_sales_date ;
			// $sales_forecast->save();
		} elseif ($sales_forecast->target_base == 'previous_3_years') {


			$forecasted_sales_date = collect($sales_forecast->last_3_years_seasonality)->flatMap(function ($data, $key) use ($quantity_growth_rate, $prices_increase_rate, $other_products_growth_rate) {
				$forecasted_quantity = (1 + (($quantity_growth_rate ?? 0) / 100)) * ($data['Sales Quantity']);
				$forecasted_price = (1 + (($prices_increase_rate ?? 0) / 100)) * ($data['Average Price']);
				if (str_contains($data['item'], 'Others') === false) {
					return [
						[
							'item' => $data['item'],
							'Sales %' => $data['Sales %'],
							'Sales Value' => $data['Sales Value'],
							'Average Price' => $data['Average Price'],
							'Sales Quantity' => $data['Sales Quantity'],
							'Forecasted Quantity' => $forecasted_quantity,
							'Forecasted Price' => $forecasted_price,
							'Forecasted Sales Value' => $forecasted_quantity * $forecasted_price
						]
					];
				} elseif (str_contains($data['item'], 'Others') === true) {

					return [
						[
							'item' => $data['item'],
							'Sales %' => $data['Sales %'],
							'Sales Value' => $data['Sales Value'],
							'Average Price' => $data['Average Price'],
							'Sales Quantity' => $data['Sales Quantity'],
							'Forecasted Quantity' => 0,
							'Forecasted Price' => 0,
							'Forecasted Sales Value' => (1 + (($other_products_growth_rate ?? 0) / 100)) * ($data['Sales Value'])
						]
					];
				}
			})->toArray();
			// $sales_forecast->last_3_years_seasonality = $forecasted_sales_date ;
			// $sales_forecast->save();

		} elseif ($sales_forecast->target_base == 'new_start') {

			$quantity_growth_rates = [];
			$prices_increase_rates = [];

			if (isset($request['quantity_growth_rates'])) {
				$quantity_growth_rates = $request['quantity_growth_rates'];
			} else {
				$quantity_growth_rates = isset($sales_forecast->forecasted_sales) ?  array_column($sales_forecast->forecasted_sales, 'quantity_growth_rates') : array_column($sales_forecast->previous_year_seasonality, 'quantity_growth_rates');
			}
			if (isset($request['prices_increase_rates'])) {
				$prices_increase_rates =  $request['prices_increase_rates'];
			} else {
				$prices_increase_rates = isset($sales_forecast->forecasted_sales) ?  array_column($sales_forecast->forecasted_sales, 'prices_increase_rates') : array_column($sales_forecast->previous_year_seasonality, 'prices_increase_rates');
			}


			$forecasted_sales_date = collect($sales_forecast->previous_year_seasonality)->flatMap(function ($data, $key) use ($quantity_growth_rates, $prices_increase_rates) {
				$forecasted_quantity = (1 + ((($quantity_growth_rates[$key]) ?? 0) / 100)) * ($data['Sales Quantity']);
				$forecasted_price = (1 + ((($prices_increase_rates[$key]) ?? 0) / 100)) * ($data['Average Price']);

				if (str_contains($data['item'], 'Others') === false) {
					return [
						[
							'item' => $data['item'],
							'Sales %' => $data['Sales %'],
							'Sales Value' => $data['Sales Value'],
							'Average Price' => $data['Average Price'],
							'Sales Quantity' => $data['Sales Quantity'],
							'Forecasted Quantity' => $forecasted_quantity,
							'quantity_growth_rates' => $quantity_growth_rates[$key] ?? null,
							'Forecasted Price' => $forecasted_price,
							'prices_increase_rates' => $prices_increase_rates[$key] ?? null,
							'Forecasted Sales Value' => $forecasted_quantity * $forecasted_price
						]
					];
				} elseif (str_contains($data['item'], 'Others') === true) {

					return [
						[
							'item' => $data['item'],
							'Sales %' => $data['Sales %'],
							'Sales Value' => $data['Sales Value'],
							'Average Price' => $data['Average Price'],
							'Sales Quantity' => $data['Sales Quantity'],
							'Forecasted Quantity' => 0,
							'quantity_growth_rates' => $quantity_growth_rates[$key] ?? null,
							'Forecasted Price' => 0,
							'prices_increase_rates' => $prices_increase_rates[$key] ?? null,
							'Forecasted Sales Value' => (1 + (($other_products_growth_rate ?? 0) / 100)) * ($data['Sales Value'])
						]
					];
				}
			})->toArray();
		}
		$sales_forecast->forecasted_sales = $forecasted_sales_date;
		$sales_forecast->save();


		if ($request->isMethod('POST')) {
			if ($sales_forecast->add_new_products == 0) {
				return redirect()->route('products.allocations.quantity', $company);
			} else {
				return redirect()->route('categories.quantity.create', $company);
			}
		} else {
			return view('client_view.quantity_forecast.forecasted_sales_values', compact('sales_forecast', 'forecasted_sales_date'));
		}
	}
	public function createCategories(Company $company, Request $request)
	{
		$sales_forecast = QuantitySalesForecast::company()->first();
		$categories = QuantityCategory::company()->where('type', 'new')->with('products')->get();


		$has_product_item = $this->fields($company);
		// Saving
		if ($request->isMethod('POST')) {
			// if there are existing saved Cats dont sve it again
			if (QuantityCategory::company()->where('type', 'existing')->count() == 0) {
				$sale_gathering_categories = SalesGathering::company()->whereNotNull('category')->where('category', '!=', '')->groupBy('category')->get()->pluck('category')->toArray();

				foreach ($sale_gathering_categories as $key => $cat) {
					QuantityCategory::create([
						'name' => $cat,
						'company_id' => $company->id,
						'type' => 'existing',
					]);
				}
			}
			// saving Action
			if ($request->submit == 'Next') {

				if (count($categories) > 0) {
					// $categories->each->delete();
					foreach ($request['category_name'] as $key => $cat) {
						if ($cat !== null && isset($categories[$key])) {
							$categories[$key]->update([
								'name' => $cat,
								'company_id' => $company->id,
								'type' => 'new',
							]);
						} elseif ($cat == null && isset($categories[$key])) {
							$categories[$key]->delete();
						} elseif (!isset($categories[$key]) && $cat !== null) {

							QuantityCategory::create([
								'name' => $cat,
								'company_id' => $company->id,
								'type' => 'new',
							]);
						}
					}
				} else {

					foreach ($request['category_name'] as $key => $cat) {
						if ($cat !== null) {
							QuantityCategory::create([
								'name' => $cat,
								'company_id' => $company->id,
								'type' => 'new',
							]);
						}
					}
				}
			}

			if ($has_product_item == true) {
				return redirect()->route('products.quantity.create', $company);
			} else {
				return redirect()->route('products.quantity.seasonality', $company);
			}
		}
		// View
		else {
			return view('client_view.quantity_forecast.categories', compact('company', 'sales_forecast', 'categories'));
		}
	}
	public function createProducts(Company $company, Request $request)
	{
		$sales_forecast = QuantitySalesForecast::company()->first();
		$categories = QuantityCategory::company()->get();
		$products = QuantityProduct::company()->where('type', 'new')->with('category')->get();
		if ($request->isMethod('POST')) {
			// if there are existing saved Cats dont sve it again
			// if (1) {
			if (QuantityProduct::company()->where('type', 'existing')->count() == 0) {
				$sales_gathering_products = SalesGathering::company()
					->whereNotNull('category')
					->where('category', '!=', '')
					->whereNotNull('product_or_service')
					->where('product_or_service', '!=', '')
					->groupBy('product_or_service')
					->get()
					->pluck('category', 'product_or_service')->toArray();

				foreach ($sales_gathering_products as $product => $cat) {
					$category = QuantityCategory::company()->where('name', $cat)->first();

					QuantityProduct::create([
						'name' => $product,
						'company_id' => $company->id,
						'category_id' => $category->id,
						'type' => 'existing',
					]);
				}
			}
			if ($request->submit == 'Save') {
				if (count($products) > 0) {
					$products->each->delete();
				}
				foreach ($request['product_name'] as $key => $product_name) {
					if ($product_name !== null && isset($request['category'][$key]) && $request['category'][$key] !== null) {
						QuantityProduct::create([
							'name' => $product_name,
							'company_id' => $company->id,
							'category_id' => $request['category'][$key],
							'type' => 'new',
						]);
					}
				}
			}
			return redirect()->route('products.seasonality.quantity', $company);
		} else {

			return view('client_view.quantity_forecast.products', compact('company', 'sales_forecast', 'categories', 'products'));
		}
	}

	public function productsSeasonality(Company $company, Request $request)
	{
		$product_seasonality = QuantityProductSeasonality::company()->get();
		$sales_forecast = QuantitySalesForecast::company()->first();
		if ($request->isMethod('POST')) {
			// Validations
			$validation['product_items_name.*'] =  'required';
			$validation['products.*'] = 'required';
			$validation['sales_target_value.*'] = 'required|numeric|min:0';
			$validation['sales_target_quantity.*'] = 'required|numeric|min:0';
			// ($sales_forecast->target_base !== 'new_start' || $sales_forecast->new_start !== 'product_target') ? 'required|numeric|min:0' : '';
			$validation['seasonality.*'] = 'required';
			$validation['percentages_total'] = [];
			foreach ($request->product_items_name as $key => $name) {

				$total_of_product_perc = $request['seasonality'][$key] == 'new_seasonality_monthly' ?
					array_sum($request['new_seasonality_monthly'][$key]) :
					array_sum($request['new_seasonality_quarterly'][$key]);
				$validation['percentages_total.' . $key] = $total_of_product_perc != 100 ? 'required' : '';
			}

			$request->validate(@$validation, [
				'percentages_total.*.required' => 'Total Percentages Must be 100%'
			]);



			if ($product_seasonality !== null && count($product_seasonality) > 0) {

				foreach ($request->product_items_name as $key => $name) {
					if (isset($product_seasonality[$key])) {
						$product_seasonality[$key]->update([
							'name' => $request->product_items_name[$key],
							'company_id' => $company->id,
							'category_id' => $request['categories'][$key],
							'product_id' => $request['products'][$key] ?? null,
							'sales_target_value' => $request['sales_target_value'][$key] ?? null,
							'sales_target_quantity' => $request['sales_target_quantity'][$key] ?? 0,
							'seasonality' => $request['seasonality'][$key],
							'seasonality_data' => $request['seasonality'][$key] == 'new_seasonality_monthly' ?
								$request['new_seasonality_monthly'][$key] :
								$request['new_seasonality_quarterly'][$key],
						]);
					} else {
						QuantityProductSeasonality::create([
							'name' => $name,
							'company_id' => $company->id,
							'category_id' => $request['categories'][$key],
							'product_id' => $request['products'][$key] ?? null,
							'sales_target_value' => $request['sales_target_value'][$key] ?? null,
							'sales_target_quantity' => $request['sales_target_quantity'][$key] ?? 0,
							'seasonality' => $request['seasonality'][$key],
							'seasonality_data' => $request['seasonality'][$key] == 'new_seasonality_monthly' ?
								$request['new_seasonality_monthly'][$key] :
								$request['new_seasonality_quarterly'][$key],
						]);
					}
				}
			} else {
				foreach ($request->product_items_name as $key => $name) {

					QuantityProductSeasonality::create([
						'name' => $name,
						'company_id' => $company->id,
						'category_id' => $request['categories'][$key],
						'product_id' => $request['products'][$key] ?? null,
						'sales_target_value' => $request['sales_target_value'][$key] ?? null,
						'sales_target_quantity' => $request['sales_target_quantity'][$key] ?? 0,
						'seasonality' => $request['seasonality'][$key],
						'seasonality_data' => $request['seasonality'][$key] == 'new_seasonality_monthly' ?
							$request['new_seasonality_monthly'][$key] :
							$request['new_seasonality_quarterly'][$key],
					]);
				}
			}
			return redirect()->route('products.allocations.quantity', $company);
		} else {



			$products = QuantityProduct::company()->with('category')->get();



			$dates = [];
			$quarter_dates = [];
			$counter = 1;
			for ($month = 0; $month < 12; $month++) {
				$date = $this->dateCalc($sales_forecast->start_date, $month);
				$dates[$date] = '';
				if ($counter % 3 == 0) {
					$quarter_dates[$date] = '';
				}
				$counter++;
			}
			$sales_forecast["quarter_dates"] = $quarter_dates;
			$sales_forecast["dates"] = $dates;
			$has_product_item = $this->fields($company);

			return view('client_view.quantity_forecast.products_seasonality', compact('company', 'sales_forecast', 'products', 'product_seasonality', 'has_product_item'));
		}
	}


	public function productsSalesTargets(Company $company, Request $request, $noReturn = false)
	{

		$sales_forecast = QuantitySalesForecast::company()->first();
		$products = QuantityProduct::company()->with('category')->get();
		$product_seasonality = QuantityProductSeasonality::company()->get();
		$modified_targets = QuantityModifiedTarget::company()->first();
		$has_product_item = $this->fields($company);
		$type = ($has_product_item === true) ? 'product_item' : 'product_or_service';
		$request['type'] = $type;

		$request['start_date'] = $sales_forecast->previous_year . '-01-01';
		$request['end_date'] = $sales_forecast->previous_year . '-12-31';

		if ($request->isMethod('POST')) {
			$use_modified_targets = ($request->use_modified_targets ?? 0);
			$validation['percentages_total'] = (($use_modified_targets == 1) && (array_sum(array_column($request->modify_sales_target, 'percentage')) != 100)) ? 'required' : '';

			$request->validate(@$validation, [
				'percentages_total.required' => 'Total Modified Sales Percentages Must be 100%'
			]);

			if ($modified_targets !== null) {
				$modified_targets->update([
					'company_id' => $company->id,
					'use_modified_targets' => $request->use_modified_targets ?? 0,
					'products_modified_targets' => $request->modify_sales_target,
					'sales_targets_percentages' => $request->sales_targets_percentages ?: 0,
					'others_target' => $request->others_target,
				]);
			} else {
				QuantityModifiedTarget::create([
					'company_id' => $company->id,
					'use_modified_targets' => $request->use_modified_targets ?? 0,
					'products_modified_targets' => $request->modify_sales_target,
					'sales_targets_percentages' => $request->sales_targets_percentages ?: 0,
					'others_target' => $request->others_target,
				]);
				$modified_targets = QuantityModifiedTarget::company()->first();
			}
		}

		if ($request->isMethod('POST')  && $request->submit === null) {
			return redirect()->route('products.allocations.quantity', $company);
		}
		$products = salesGathering::company()
			->whereNotNull($type)
			->where($type, '!=', '')
			->whereBetween('date', [($sales_forecast->previous_year . '-01-01'), $sales_forecast->previous_year . '-12-31'])
			->groupBy($type)
			->selectRaw($type)
			->get()
			->pluck($type)
			->toArray();

		$products_data = null;
		if ($sales_forecast->seasonality == "last_3_years") {
			$request['start_date']  = ($sales_forecast->previous_year - 2) . '-01-01';
			$request['end_date']    = $sales_forecast->previous_year . '-12-31';
			$products_data = collect(DB::select(DB::raw(
				"
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value,service_provider_name,product_item
                FROM sales_gathering
                WHERE ( company_id = '" . $company->id . "'AND product_item IS NOT NULL  AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
                ORDER BY id "
			)))->whereIn($type, $products);
		} elseif ($sales_forecast->seasonality == "previous_year") {

			$request['start_date']  = $sales_forecast->previous_year . '-01-01';
			$request['end_date']    = $sales_forecast->previous_year . '-12-31';
		}




		if (($request->submit == 'Show') || (count(($modified_targets->others_target ?? [])) > 0) || (($request->isMethod('GET')) && isset($modified_targets) && $modified_targets !== null)) {


			// $product_item_breakdown_data = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'withOthers');
			$product_item_breakdown_data = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'withOthers', $products_data);
			$product_item_breakdown_data = $this->addingOthersToData($product_item_breakdown_data, $modified_targets->others_target);
			// modified_targets

			$products_used = array_column($product_item_breakdown_data, 'item');
			if (count(($modified_targets->others_target ?? [])) > 0) {

				foreach ($modified_targets->others_target as $key => $value) {
					if (false !== $found = array_search($value, $products_used)) {
						unset($products_used[$found]);
					}
				}
			}
			$selector_products = SalesGathering::company()
				->whereNotNull($type)
				->where($type, '!=', '')
				->whereNotIn($type, $products_used)
				->groupBy($type)
				->pluck($type)
				->toArray();


			if ($noReturn) {
				return;
			}


			return view('client_view.quantity_forecast.products_sales_targets', compact(
				'company',
				'product_item_breakdown_data',
				'product_seasonality',
				'sales_forecast',
				'products',
				'selector_products',
				'modified_targets',
				'has_product_item'
			));
		}

		if ($request->isMethod('GET')) {

			// $product_item_breakdown_data = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'array');
			$product_item_breakdown_data = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'array', $products_data);
			$products_used = array_column($product_item_breakdown_data, 'item');
			$selector_products = SalesGathering::company()
				->whereNotNull($type)
				->where($type, '!=', '')
				->whereNotIn($type, $products_used)
				->groupBy($type)
				->pluck($type)
				->toArray();
			if ($noReturn) {
				return;
			}

			return view('client_view.quantity_forecast.products_sales_targets', compact(
				'company',
				'product_item_breakdown_data',
				'product_seasonality',
				'sales_forecast',
				'products',
				'selector_products',
				'modified_targets',
				'has_product_item'
			));
		}
	}
	

	

	
	
	public function productsAllocations(Company $company, Request $request, $result = 'view', $noReturn = false)
	{
		$has_product_item = $this->fields($company);
		$type = ($has_product_item === true) ? 'product_item' : 'product_or_service';
		if ($request->isMethod('POST') && $result == 'view') {
			if ($noReturn) {
				return;
			}
			return redirect()->route('allocations.quantity', $company);
		}
		$sales_forecast = QuantitySalesForecast::company()->first();
		$products_seasonality = QuantityProductSeasonality::company()->get();
		$year = date('Y', strtotime($sales_forecast->start_date));

		$monthly_dates = [];
		$quarter_dates = [];
		$counter = 1;
		for ($month = 0; $month < 12; $month++) {
			$date = $this->dateCalc($sales_forecast->start_date, $month);
			$monthly_dates[$date] = '';
			if ($counter % 3 == 0) {
				$quarter_dates[$date] = '';
			}
			$counter++;
		}

		$new_products_seasonalities = [];

		$hasProductsNotDeleted = \getNumberOfProductsItemsQuantity($company->id);

		foreach ($products_seasonality as $key => $product_seasonality) {
			$sales_target_value = $product_seasonality->sales_target_value * $product_seasonality->sales_target_quantity;
			$seasonality        = $product_seasonality->seasonality;
			$seasonality_data   = $product_seasonality->seasonality_data;
			// if( $hasProductsNotDeleted){
			$new_products_seasonalities[$product_seasonality->name] =


				$this->seasonalityFun($seasonality, $seasonality_data, $monthly_dates, $sales_target_value, $product_seasonality, $year);

			// }

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Change Q
		$total_sales_targets_values = $sales_forecast['add_new_products'] == 0 ? 0 : collect($products_seasonality)->sum('sales_target_value');
		$existing_products_sales_targets = $sales_forecast->sales_target - $total_sales_targets_values;

		$forecast_seasonality_data = [];
		$used_dates = [];
		// if ($sales_forecast->seasonality == 'previous_year') {
		//     $forecast_seasonality_data = $this->sorting($sales_forecast->previous_year_seasonality);
		//     $used_dates = $forecast_seasonality_data;
		// } elseif ($sales_forecast->seasonality == 'last_3_years') {
		//     $forecast_seasonality_data = $this->sorting($sales_forecast->last_3_years_seasonality);
		//     $used_dates = $forecast_seasonality_data;
		// } else {
		//     $used_dates = $monthly_dates;
		//     $forecast_seasonality_data = $this->sorting($sales_forecast->new_seasonality);
		// }

		// $existing_products_seasonalities = $this->seasonalityFun(
		//     $sales_forecast->seasonality,
		//     $forecast_seasonality_data,
		//     $used_dates,
		//     $existing_products_sales_targets,
		//     $year

		// );
		$existing_products_seasonalities['Totals'] = [];
		// array_sum($existing_products_seasonalities);
		$new_products_totals = $this->finalTotal($new_products_seasonalities);
		//salah
		// if(! $hasProductsNotDeleted){
		//     $new_products_totals = [];
		//     $new_products_seasonalities = [];

		// }
		//end

		$existing_products_targets = [];



		if ($result == 'total_company_sales_target'||($result == 'view') || ($result == 'total_sales_target') || ($result == 'total_sales_target_data') || $result=='detailed_company_sales_target') {
			// $modified_targets = QuantityModifiedTarget::company()->first();


			$products = salesGathering::company()
				->whereNotNull($type)
				->where($type, '!=', '')
				->whereBetween('date', [($sales_forecast->previous_year . '-01-01'), $sales_forecast->previous_year . '-12-31'])
				->groupBy($type)
				->selectRaw($type)
				->get()
				->pluck($type)
				->toArray();

			$request['type'] = $type;
			// $products_data = null;
			if ($sales_forecast->seasonality == "last_3_years") {

				$request['start_date']  = ($sales_forecast->previous_year - 2) . '-01-01';
				$request['end_date']    = $sales_forecast->previous_year . '-12-31';
				// $products_data = collect(DB::select(DB::raw(
				//     "
				//     SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value,service_provider_name,product_item
				//     FROM sales_gathering
				//     WHERE ( company_id = '" . $company->id . "'AND product_item IS NOT NULL  AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
				//     ORDER BY id "
				// )))->whereIn($type, $products);
			} elseif ($sales_forecast->seasonality == "previous_year") {

				$request['start_date']  = $sales_forecast->previous_year . '-01-01';
				$request['end_date']    = $sales_forecast->previous_year . '-12-31';
			}



			$product_item_breakdown_data = $sales_forecast->forecasted_sales;
			$products_items = array_column($product_item_breakdown_data, 'item');


			$last_key = (array_key_last($products_items));
			$products_items_monthly_values = [];

			// if (isset($modified_targets->use_modified_targets) && $modified_targets->use_modified_targets == 1) {
			//     $products_items_monthly_values =  $modified_targets->products_modified_targets;
			//     $products_items_monthly_values =  array_combine(array_keys($products_items_monthly_values), array_column($products_items_monthly_values, 'value'));
			// }else{

			// }
			$product_item_breakdown_data_items = array_combine(array_column($product_item_breakdown_data, 'item'), array_column($product_item_breakdown_data, 'Sales Quantity'));

			$modified_seasonality = QuantityModifiedSeasonality::company()->first();
			// existing_products_targets

			$others_sales_values = SalesGathering::company()
				->whereNotNull('product_item')
				->whereBetween('date', [($request['start_date']), $request['end_date']])
				->where('product_item', '!=', '')
				->whereNotIn('product_item', $products_items)
				->sum('net_sales_value');


			collect($product_item_breakdown_data_items)->map(function ($item, $key) use (&$product_item_breakdown_data_items, $others_sales_values) {
				if (strstr($key, 'Others') !== false) {
					$product_item_breakdown_data_items[$key] = $others_sales_values;
				}
			});
			$products_items_monthly_percentage =  (new QuantitySeasonalityReport)->productsItemsData($request, $company, $sales_forecast, $product_item_breakdown_data_items, $type);
			if ($modified_seasonality === null || count($product_item_breakdown_data_items) != (count(($modified_seasonality->original_seasonality ?? [])))) {

				if ($modified_seasonality === null) {
					QuantityModifiedSeasonality::create([
						'company_id' => $company->id,
						'original_seasonality' => $products_items_monthly_percentage,
						'use_modified_seasonality' => 0
					]);
				} else {
					$modified_seasonality->update([
						'original_seasonality' => $products_items_monthly_percentage,
					]);
				}
			}
			if (isset($modified_seasonality) && $modified_seasonality->use_modified_seasonality == 1) {
				$products_items_monthly_percentage = $modified_seasonality->modified_seasonality;
			} elseif (isset($modified_seasonality) && $modified_seasonality->use_modified_seasonality == 0) {
				$products_items_monthly_percentage = $modified_seasonality->original_seasonality;
			}


			// $total = (isset($modified_targets->use_modified_targets) && $modified_targets->use_modified_targets == 1 )? array_sum($products_items_monthly_values) : array_sum(array_column($product_item_breakdown_data, 'Sales Quantity'));
			$total =  array_sum(array_column($product_item_breakdown_data, 'Sales Quantity'));

			$totals_per_month = [];

			

			foreach ($product_item_breakdown_data as $key => $product_data) {

				$total_existing_targets = 0;
				// $target = (isset($modified_targets->use_modified_targets) && $modified_targets->use_modified_targets == 1 )? $products_items_monthly_values[$product_data['item']] : $product_data['Sales Quantity'];
				$target =  $product_data['Sales Quantity'];
				$target_percentage = $total == 0 ? 0 : $target / $total;

				// $existing_target_per_product = $target_percentage * $product_data['Forecasted Quantity'];
				foreach ((array)$monthly_dates as $date => $value) {
					$date = date('M-Y', strtotime($date));
					$month = date('F', strtotime($date));

					$item_name = $product_data['item'];

					// $item_name = strstr($product_data['item'], 'Others') !== false ? 'Others' : $product_data['item'];

					// $percentage = $products_items_monthly_percentage[$item_name][$month] ?? 0;
					if (strstr($product_data['item'], 'Others') !== false) {

						$percentage = isset($products_items_monthly_percentage[$item_name][$month]) ? $products_items_monthly_percentage[$item_name][$month] : ($products_items_monthly_percentage['Others'][$month] ?? 0);
					} else {
						$percentage = $products_items_monthly_percentage[$item_name][$month] ?? 0;
					}


					if (strstr($product_data['item'], 'Others') === false) {

						$result_per_month = $product_data['Forecasted Quantity'] * $product_data['Forecasted Price'] * $percentage;
					} else {

						$result_per_month = $product_data['Forecasted Sales Value'] * $percentage;
					}
					$existing_products_targets[$product_data['item']][$date] = $result_per_month;
					$totals_per_month[$date] = $result_per_month + ($totals_per_month[$date] ?? 0);
					$total_existing_targets += $result_per_month;
				}
			}
			if ($result ==  'total_sales_target') {

				unset($existing_products_seasonalities['Totals']);

				$total_company_sales_target = $this->finalTotal([$new_products_totals, $existing_products_seasonalities]);
				arsort($total_company_sales_target);
				$targets = array_merge($existing_products_targets, $new_products_seasonalities);


				if ($noReturn) {
					return;
				}

				return $targets;
			} elseif ($result == 'total_sales_target_data') {
				return [
					'existing' => $totals_per_month,
					'new' => $new_products_totals
				];
			}


			$totals_per_month = $totals_per_month ?? [];


			if ($noReturn) {
				return;
			}
			if($result == 'detailed_company_sales_target'){
			
				unset($existing_products_targets['Totals']);
				$total_company_sales_target = $this->finalTotal([$new_products_totals, $existing_products_targets]);
				return [
					'existing' => $existing_products_targets,
					'new' => $new_products_totals,
					'total' => $total_company_sales_target,
					'totalExisting'=>$this->finalTotal($existing_products_targets),
					'totalNew'=>$this->finalTotal($new_products_totals)
				];
				
			}
			if($result == 'total_company_sales_target'){
					unset($existing_products_targets['Totals']);
					
					
			return $this->finalTotal([$new_products_totals, $existing_products_targets]);
			}
			return view('client_view.quantity_forecast.products_allocations', compact(
				'company',
				'monthly_dates',
				'existing_products_seasonalities',
				'new_products_seasonalities',
				'sales_forecast',
				'new_products_totals',
				'products_seasonality',
				'total_sales_targets_values',
				'products_items_monthly_values',
				'product_item_breakdown_data',

				'has_product_item',
				'products_items_monthly_percentage',
				'existing_products_targets',
				'existing_products_sales_targets',
				'totals_per_month'
			));
		} elseif ($result == 'total_company_sales_target') {
			// unset($existing_products_seasonalities['Totals']);
			// return $this->finalTotal([$new_products_totals, $existing_products_seasonalities]);
		}
		 elseif ($result == 'detailed_company_sales_target') {
			// unset($existing_products_seasonalities['Totals']);

			// $total_company_sales_target = $this->finalTotal([$new_products_totals, $existing_products_seasonalities]);
			// return [
			// 	'existing' => $existing_products_seasonalities,
			// 	'new' => $new_products_totals,
			// 	'total' => $total_company_sales_target
			// ];
		} else {
			unset($existing_products_seasonalities['Totals']);
			$total_monthly_targets =  $this->finalTotal([$existing_products_seasonalities]);
			return $total_monthly_targets;
		}
	}

	public function addingOthersToData($product_item_breakdown_data, $others_target)
	{

		$key_num = 0;
		$report_data =  collect($product_item_breakdown_data)->sortByDesc(function ($data, $key) use ($key_num) {
			return [$data['Sales Value']];
		});

		$added_products = collect($report_data)->whereIn('item', $others_target);

		$viewing_data = $report_data->toArray();
		$total_of_all_data = array_sum(array_column($viewing_data, 'Sales Value'));

		$top_100 = $report_data->take(100);
		$others_count = count($report_data) - count($top_100) - count($added_products);
		$report_view_data = $top_100->toArray();
		$report_view_data =   $report_view_data + $added_products->toArray();

		$others_total = $total_of_all_data - array_sum(array_column($report_view_data, 'Sales Value'));
		if ($others_total > 0) {
			array_push($report_view_data, ['item' => 'Others ' . $others_count, 'Sales Value' => $others_total]);
		}
		$key_num = 0;
		$final_data = [];

		array_walk($report_view_data, function ($value, $key) use (&$key_num, &$final_data) {
			$final_data[$key_num] = $value;
			$key_num++;
		});
		return $final_data;
	}
	public function seasonalityFun($seasonality, $seasonality_data, $monthly_dates, $sales_target_value, $year)
	{
		$new_products_seasonalities = [];
		$counter = 1;
		if ($seasonality == 'new_seasonality_quarterly') {
			$quarters_percentages = [];
			foreach ($seasonality_data as $date => $value) {
				$quarters_percentages[number_format(date('m', strtotime($date)))] = $value;
			}
			$num_of_quarter = 0;
			foreach ((array)$monthly_dates as $date => $value) {
				$month = date('m', strtotime($date));
				if ($month <= 3) {
					$num_of_quarter = 3;
				} elseif ($month <= 6) {
					$num_of_quarter = 6;
				} elseif ($month <= 9) {
					$num_of_quarter = 9;
				} elseif ($month <= 12) {
					$num_of_quarter = 12;
				}
				$seasonality_percentage = ($quarters_percentages[$num_of_quarter] ?? 0) / 3;
				$seasonality_percentage = (($seasonality_percentage) / 100);

				$new_products_seasonalities[date('M-Y', strtotime($date))] = $sales_target_value * $seasonality_percentage;
				$counter++;
			}
		} elseif ($seasonality == 'new_seasonality_monthly' || $seasonality == 'previous_year' || $seasonality == 'last_3_years') {



			foreach ((array)$monthly_dates as $date => $value) {
				$current_seasonality = ($seasonality_data[$date] ?? 0);
				if ($seasonality == 'previous_year') {
					$month  = date('M', strtotime($date));
					$date = $month . '-' . $year;
				} elseif ($seasonality == 'last_3_years') {

					$date_text  = '01-' . $date . '-' . $year;
					$date = date('M-Y', strtotime($date_text));
				} else {
					$date = date('M-Y', strtotime($date));
				}
				$new_products_seasonalities[$date] = $sales_target_value * ($current_seasonality / 100);
			}
		}
		(arsort($new_products_seasonalities));
		return $new_products_seasonalities;
	}
}
