<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Analysis\SalesGathering\ProductsAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\salesReport;
use App\Models\Category;
use App\Models\Company;
use App\Models\CustomizedFieldsExportation;
use App\Models\Log;
use App\Models\ModifiedSeasonality;
use App\Models\ModifiedTarget;
use App\Models\NewProductAllocationBase;
use App\Models\Product;
use App\Models\ProductSeasonality;
use App\Models\SalesForecast;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class SalesForecastReport
{
	use GeneralFunctions;
	public function fields($company)
	{
		$fields = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');

		return (false !== $found = array_search('Product Item', $fields));
	}
	public function result(Company $company, Request $request)
	{
		$sales_forecast = SalesForecast::company()->first();
		$has_product_item = $this->fields($company);

		if ($sales_forecast === null) {
			$start_date = now()->startOfYear()->format('Y-m-d');
			$end_date = now()->endOfYear()->format('Y-m-d');

			if ($request->isMethod('GET')) {
		Log::storeNewLogRecord('enterSection',null,__('Sales Forecast Value Base'));
				
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
			$end_date_for_report = ($start_year - 1) . '-12-01';
			$request['end_date'] = $start_date;
			$salesReport = (new salesReport)->result($request, $company, 'array');
			if(!count($salesReport['total_full_data'])){
				return redirect()->route('salesGatheringImport',[
					'company'=>$company->id,
					'model'=>'SalesGathering'
				])->with('fail',__('Please at least upload pervious Year Sales Data'));
			}
			
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

			// Creating Array For View

			$sales_forecast["start_date"] = $start_date;
			$sales_forecast["end_date"] =   $end_date;
			$sales_forecast["previous_year"] = $previous_year;
			$sales_forecast["previous_1_year_sales"] = $previous_1_year_sales;
			$sales_forecast["previous_year_gr"] = $previous_year_gr;
			$sales_forecast["average_last_3_years"] = $average_last_3_years;
			$sales_forecast["previous_year_seasonality"] = $previous_year_seasonality;
			$sales_forecast["last_3_years_seasonality"] = $last_3_years_seasonality;
		} else {
			$start_date = $sales_forecast->start_date;
		}
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


		$sales_forecast['previous_year_seasonality'] = $this->sorting($sales_forecast['previous_year_seasonality']);
		$previousYearSeasonality = ($sales_forecast['previous_year_seasonality'] ?? []) ;
		
		$sales_forecast['previous_year_seasonality'] = $this->sortDates($previousYearSeasonality);
$sales_forecast['previous_year_seasonality'] = $previousYearSeasonality;
		$sales_forecast['last_3_years_seasonality'] = $this->sorting($sales_forecast['last_3_years_seasonality']);
		$sales_forecast['last_3_years_seasonality'] = sortMonthsByItsNames($sales_forecast['last_3_years_seasonality']);
		return view(
			'client_view.forecast.sales_forecast',
			compact('company', 'sales_forecast', 'has_product_item')
		);
	}
	
	public function sortDates(array &$items){
	    
	     uksort($items, function ($a, $b) use(&$items) {
	    
        return \DateTime::createFromFormat('d-m-Y', $a) <=> \DateTime::createFromFormat('d-m-Y', $b);
});
	    
	}

	public function save(Company $company, Request $request, $noReturn = false)
	{

		if (isset($request['summary_report'])) {
			return (new SummaryController())->goToSummaryReport($request, $company);
		}
		$sales_forecast = SalesForecast::company()->first();

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
				'growth_rate' => $request['target_base'] == 'previous_year' || $request['target_base'] == 'previous_3_years' ? 'required|numeric|min:0' : '',
				'sales_target' => ($request['target_base'] == 'previous_year' || $request['target_base'] == 'previous_3_years')
					|| ($request['new_start'] == 'annual_target' && $request['new_start'] == 'annual_target') ? 'required|numeric|min:1' : '',
				'new_start' => $request['target_base'] == 'new_start' ? 'required' : '',
				'seasonality' =>   'sometimes|required',
				'number_of_products' => $request['add_new_products'] == 1 ? 'required|numeric|min:1' : '',

			],
			[
				'sales_target.min' => 'The sales target must be greater than zero'
			]
		);
		$sales_forecast = $sales_forecast !== null ? $sales_forecast : new SalesForecast;
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
			$sales_forecast->growth_rate = $request['growth_rate'];
			$sales_forecast->sales_target = $request['sales_target'];
			$sales_forecast->new_start = null;
		} elseif ($request['target_base'] == 'new_start') {
			$sales_forecast->new_start = $request['new_start'];
			$sales_forecast->growth_rate = 0;
			$sales_forecast->sales_target = ($request['new_start'] == 'annual_target') ? $request['sales_target'] : 0;
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

		if ($request['add_new_products'] == 0) {
			return redirect()->route('products.sales.targets', $company);
		} else {
			return redirect()->route('categories.create', $company);
		}
	}
	public function createCategories(Company $company, Request $request)
	{
		$sales_forecast = SalesForecast::company()->first();
		$categories = Category::company()->where('type', 'new')->with('products')->get();
		$has_product_item = $this->fields($company);
		// Saving
		if ($request->isMethod('POST')) {
			// if there are existing saved Cats dont sve it again
			if (Category::company()->where('type', 'existing')->count() == 0) {
				$sale_gathering_categories = SalesGathering::company()->whereNotNull('category')->where('category', '!=', '')->groupBy('category')->get()->pluck('category')->toArray();

				foreach ($sale_gathering_categories as $key => $cat) {
					Category::create([
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

							Category::create([
								'name' => $cat,
								'company_id' => $company->id,
								'type' => 'new',
							]);
						}
					}
				} else {

					foreach ($request['category_name'] as $key => $cat) {
						if ($cat !== null) {
							Category::create([
								'name' => $cat,
								'company_id' => $company->id,
								'type' => 'new',
							]);
						}
					}
				}
			}

			if ($has_product_item == true) {
				return redirect()->route('products.create', $company);
			} else {
				return redirect()->route('products.seasonality', $company);
			}
		}
		// View
		else {
			return view('client_view.forecast.categories', compact('company', 'sales_forecast', 'categories'));
		}
	}
	public function createProducts(Company $company, Request $request)
	{
		$sales_forecast = SalesForecast::company()->first();
		$categories = Category::company()->get();
		$products = Product::company()->where('type', 'new')->with('category')->get();
		// Saving
		if ($request->isMethod('POST')) {
			// if there are existing saved Cats dont sve it again
			// if (1) {
			if (Product::company()->where('type', 'existing')->count() == 0) {
				$sales_gathering_products = SalesGathering::company()
					->whereNotNull('category')
					->where('category', '!=', '')
					->whereNotNull('product_or_service')
					->where('product_or_service', '!=', '')
					->groupBy('product_or_service')
					->get()
					->pluck('category', 'product_or_service')->toArray();

				foreach ($sales_gathering_products as $product => $cat) {
					$category = Category::company()->where('name', $cat)->first();

					Product::create([
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
						Product::create([
							'name' => $product_name,
							'company_id' => $company->id,
							'category_id' => $request['category'][$key],
							'type' => 'new',
						]);
					}
				}
			}
			return redirect()->route('products.seasonality', $company);
		} else {

			return view('client_view.forecast.products', compact('company', 'sales_forecast', 'categories', 'products'));
		}
	}

	public function productsSeasonality(Company $company, Request $request)
	{
		$product_seasonality = ProductSeasonality::company()->get();
		$sales_forecast = SalesForecast::company()->first();
		if ($request->isMethod('POST')) {
			// Validations
			$validation['product_items_name.*'] =  'required';
			$validation['products.*'] = 'required';
			$validation['sales_target_value.*'] = 'required|numeric|min:0';
			$validation['sales_target_percentage.*'] = ($sales_forecast->target_base !== 'new_start' || $sales_forecast->new_start !== 'product_target') ? 'required|numeric|min:0' : '';
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
							'sales_target_percentage' => $request['sales_target_percentage'][$key] ?? 0,
							'seasonality' => $request['seasonality'][$key],
							'seasonality_data' => $request['seasonality'][$key] == 'new_seasonality_monthly' ?
								$request['new_seasonality_monthly'][$key] :
								$request['new_seasonality_quarterly'][$key],
						]);
					} else {
						ProductSeasonality::create([
							'name' => $name,
							'company_id' => $company->id,
							'category_id' => $request['categories'][$key],
							'product_id' => $request['products'][$key] ?? null,
							'sales_target_value' => $request['sales_target_value'][$key] ?? null,
							'sales_target_percentage' => $request['sales_target_percentage'][$key] ?? 0,
							'seasonality' => $request['seasonality'][$key],
							'seasonality_data' => $request['seasonality'][$key] == 'new_seasonality_monthly' ?
								$request['new_seasonality_monthly'][$key] :
								$request['new_seasonality_quarterly'][$key],
						]);
					}
				}
			} else {
				foreach ($request->product_items_name as $key => $name) {

					ProductSeasonality::create([
						'name' => $name,
						'company_id' => $company->id,
						'category_id' => $request['categories'][$key],
						'product_id' => $request['products'][$key] ?? null,
						'sales_target_value' => $request['sales_target_value'][$key] ?? null,
						'sales_target_percentage' => $request['sales_target_percentage'][$key] ?? 0,
						'seasonality' => $request['seasonality'][$key],
						'seasonality_data' => $request['seasonality'][$key] == 'new_seasonality_monthly' ?
							$request['new_seasonality_monthly'][$key] :
							$request['new_seasonality_quarterly'][$key],
					]);
				}
			}
			return redirect()->route('products.sales.targets', $company);
		} else {



			$products = Product::company()->with('category')->get();



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

			return view('client_view.forecast.products_seasonality', compact('company', 'sales_forecast', 'products', 'product_seasonality', 'has_product_item'));
		}
	}


	public function productsSalesTargets(Company $company, Request $request, $noReturn = false)
	{

		$sales_forecast = SalesForecast::company()->first();
		$products = Product::company()->with('category')->get();
		$product_seasonality = ProductSeasonality::company()->get();
		$modified_targets = ModifiedTarget::company()->first();
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
				ModifiedTarget::create([
					'company_id' => $company->id,
					'use_modified_targets' => $request->use_modified_targets ?? 0,
					'products_modified_targets' => $request->modify_sales_target,
					'sales_targets_percentages' => $request->sales_targets_percentages ?: 0,
					'others_target' => $request->others_target,
				]);
				$modified_targets = ModifiedTarget::company()->first();
			}
		}

		if ($request->isMethod('POST')  && $request->submit === null) {
			return redirect()->route('products.allocations', $company);
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
				->whereBetween('date', [$request['start_date'], $request['end_date']])
				->whereNotIn($type, $products_used)
				->groupBy($type)
				->pluck($type)
				->toArray();

			if ($noReturn) {
				return;
			}


			return view('client_view.forecast.products_sales_targets', compact(
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

			return view('client_view.forecast.products_sales_targets', compact(
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
			return redirect()->route('allocations', $company);
		}
		$sales_forecast = SalesForecast::company()->first();
		$products_seasonality = ProductSeasonality::company()->get();
		$year = $sales_forecast ? date('Y', strtotime($sales_forecast->start_date)) : null;
		if(!$year){
			return ;
		}
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

		$hasProductsNotDeleted = \getNumberOfProductsItems($company->id);

		foreach ($products_seasonality as $key => $product_seasonality) {
			$sales_target_value = $product_seasonality->sales_target_value;
			$seasonality        = $product_seasonality->seasonality;
			$seasonality_data   = $product_seasonality->seasonality_data;
			// if( $hasProductsNotDeleted){
			$new_products_seasonalities[$product_seasonality->name] =


				$this->seasonalityFun($seasonality, $seasonality_data, $monthly_dates, $sales_target_value, $product_seasonality, $year);

		}

		//////////////////////////////////////////////////////////////////////////////////////////////////

		$total_sales_targets_values = $sales_forecast['add_new_products'] == 0 ? 0 : collect($products_seasonality)->sum('sales_target_value');
		$existing_products_sales_targets = $sales_forecast->sales_target - $total_sales_targets_values;

		$forecast_seasonality_data = [];
		$used_dates = [];
		if ($sales_forecast->seasonality == 'previous_year') {
			$forecast_seasonality_data = $this->sorting($sales_forecast->previous_year_seasonality);
			$used_dates = $forecast_seasonality_data;
		} elseif ($sales_forecast->seasonality == 'last_3_years') {
			$forecast_seasonality_data = $this->sorting($sales_forecast->last_3_years_seasonality);
			$used_dates = $forecast_seasonality_data;
		} else {
			$used_dates = $monthly_dates;
			$forecast_seasonality_data = $this->sorting($sales_forecast->new_seasonality);
		}

		$existing_products_seasonalities = $this->seasonalityFun(
			$sales_forecast->seasonality,
			$forecast_seasonality_data,
			$used_dates,
			$existing_products_sales_targets,
			$year

		);

		$existing_products_seasonalities['Totals'] = array_sum($existing_products_seasonalities);
		$new_products_totals = $this->finalTotal($new_products_seasonalities);
		//salah
		// if(! $hasProductsNotDeleted){
		//     $new_products_totals = [];
		//     $new_products_seasonalities = [];

		// }
		//end



		if (($result == 'view') || ($result == 'total_sales_target')) {
			$modified_targets = ModifiedTarget::company()->first();


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


			$product_item_breakdown_data = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'withOthers', $products_data);
			$product_item_breakdown_data = $this->addingOthersToData($product_item_breakdown_data, $modified_targets->others_target);

			$products_items = array_column($product_item_breakdown_data, 'item');
			$last_key = (array_key_last($products_items));
			$products_items_monthly_values = [];
			if ($modified_targets->use_modified_targets == 1) {
				$products_items_monthly_values =  $modified_targets->products_modified_targets;
				$products_items_monthly_values =  array_combine(array_keys($products_items_monthly_values), array_column($products_items_monthly_values, 'value'));
			}
			$product_item_breakdown_data_items = array_combine(array_column($product_item_breakdown_data, 'item'), array_column($product_item_breakdown_data, 'Sales Value'));

			$modified_seasonality = ModifiedSeasonality::company()->first();




			$products_items_monthly_percentage = [];
			if ($modified_seasonality === null || count($product_item_breakdown_data_items) != (count(($modified_seasonality->original_seasonality ?? [])))) {

				$products_items_monthly_percentage =  (new SeasonalityReport)->productsItemsData($request, $company, $sales_forecast, $product_item_breakdown_data_items, $type);
				if ($modified_seasonality === null) {
					ModifiedSeasonality::create([
						'company_id' => $company->id,
						'original_seasonality' => $products_items_monthly_percentage,
						'use_modified_seasonality' => 0
					]);
				} else {
					$modified_seasonality->update([
						'original_seasonality' => $products_items_monthly_percentage,
					]);
				}
			} elseif (isset($modified_seasonality) && $modified_seasonality->use_modified_seasonality == 1) {

				$products_items_monthly_percentage = $modified_seasonality->modified_seasonality;
			} elseif (isset($modified_seasonality) && $modified_seasonality->use_modified_seasonality == 0) {

				$products_items_monthly_percentage = $modified_seasonality->original_seasonality;
			}


			$total = $modified_targets->use_modified_targets == 1 ? array_sum($products_items_monthly_values) : array_sum(array_column($product_item_breakdown_data, 'Sales Value'));

			$totals_per_month = [];

			$existing_products_targets = [];
			foreach ($product_item_breakdown_data as $key => $product_data) {

				$total_existing_targets = 0;
				$target = $modified_targets->use_modified_targets == 1 ? $products_items_monthly_values[$product_data['item']] : $product_data['Sales Value'];
				$target_percentage = $total == 0 ? 0 : $target / $total;
				$existing_target_per_product = $target_percentage * $existing_products_sales_targets;
				foreach ((array)$monthly_dates as $date => $value) {
					$date = date('M-Y', strtotime($date));
					$month = date('F', strtotime($date));

					$item_name = $product_data['item'];

					// $item_name = strstr($product_data['item'], 'Others') !== false ? 'Others' : $product_data['item'];
					if (strstr($product_data['item'], 'Others') !== false) {

						$percentage = isset($products_items_monthly_percentage[$item_name][$month]) ? $products_items_monthly_percentage[$item_name][$month] : ($products_items_monthly_percentage['Others'][$month] ?? 0);
					} else {
						$percentage = $products_items_monthly_percentage[$item_name][$month] ?? 0;
					}


					$result_per_month = $existing_target_per_product * $percentage;
					$existing_products_targets[$product_data['item']][$date] = $result_per_month;
					$totals_per_month[$date] = $result_per_month + ($totals_per_month[$date] ?? 0);
					$total_existing_targets += $result_per_month;
				}
			}


			// total_company_sales_target
			if ($result ==  'total_sales_target') {

				unset($existing_products_seasonalities['Totals']);

				$total_company_sales_target = $this->finalTotal([$new_products_totals, $existing_products_seasonalities]);
				arsort($total_company_sales_target);
				$targets = array_merge($existing_products_targets, $new_products_seasonalities);

				if ($noReturn) {
					return;
				}

				return $targets;
			}


			$totals_per_month = $totals_per_month ?? [];


			if ($noReturn) {
				return;
			}
			return view('client_view.forecast.products_allocations', compact(
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
				'modified_targets',
				'has_product_item',
				'products_items_monthly_percentage',
				'existing_products_targets',
				'existing_products_sales_targets',
				'totals_per_month'
			));
		} elseif ($result == 'total_company_sales_target') {
			unset($existing_products_seasonalities['Totals']);
			return $this->finalTotal([$new_products_totals, $existing_products_seasonalities]);
		} elseif ($result == 'detailed_company_sales_target') {

			unset($existing_products_seasonalities['Totals']);

			$total_company_sales_target = $this->finalTotal([$new_products_totals, $existing_products_seasonalities]);
			return [
				'existing' => $existing_products_seasonalities,
				'new' => $new_products_totals,
				'total' => $total_company_sales_target
			];
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

		$top_50 = $report_data->take(50);
		$others_count = count($report_data) - count($top_50) - count($added_products);
		$report_view_data = $top_50->toArray();
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
