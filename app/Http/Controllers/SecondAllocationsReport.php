<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport;
use App\Http\Controllers\SalesForecastReport;
use App\Models\AllocationSetting;
use App\Models\Company;
use App\Models\CustomizedFieldsExportation;
use App\Models\ModifiedSeasonality;
use App\Models\ModifiedTarget;
use App\Models\Product;
use App\Models\ProductSeasonality;
use App\Models\SalesForecast;
use App\Models\SalesGathering;
use App\Models\SecondAllocationSetting;
use App\Models\SecondExistingProductAllocationBase;
use App\Models\SecondNewProductAllocationBase;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SecondAllocationsReport
{
    use GeneralFunctions;
    public function fields($company)
    {
        $fields = CustomizedFieldsExportation::company()->first()->fields ?? [];

        return (false !== $found = array_search('product_item', $fields));
    }
    public function allocationSettings(Request $request, Company $company)
    {
        $sales_forecast = SalesForecast::company()->first();
        $allocations_setting = SecondAllocationSetting::company()->first();
        $first_allocations_setting = AllocationSetting::company()->first();
        $sales_targets = [];
        if ($request->isMethod('POST')) {
              $allocation_type = $request->get('allocation_base');
        $countAllocationTypeForCompany = countExistingTypeFor($allocation_type , $company);
        if(!($countAllocationTypeForCompany) && ! $request->filled('add_new_items'))
        {
            return redirect()->back()->with('fail' , failAllocationMessage($allocation_type) );
        }

            $request->validate([
                'allocation_base' => 'required',
                'breakdown' => 'sometimes|required',
                'number_of_items' => $request->add_new_items  == 1 ? 'required' : '',

            ]);
            if ($allocations_setting === null) {
                SecondAllocationSetting::create([
                    'allocation_base' => $request->allocation_base,
                    'company_id' => $company->id,
                    'breakdown' => $request->breakdown,
                    'add_new_items' => $request->add_new_items  ?? 0,
                    'number_of_items' => $request->add_new_items  == 1 ? $request->number_of_items : 0,
                ]);
            } else {
                $allocations_setting->update([
                    'allocation_base' => $request->allocation_base,
                    'company_id' => $company->id,
                    'breakdown' => $request->breakdown,
                    'add_new_items' => $request->add_new_items  ?? 0,
                    'number_of_items' => $request->add_new_items  == 1 ? $request->number_of_items : 0,
                ]);
            }
            return redirect()->route('second.new.product.allocation.base', $company);
        } else {

            return view('client_view.forecast.second_allocations', compact(
                'company',
                'sales_forecast',
                'sales_targets',
                'allocations_setting',
                'first_allocations_setting'
            ));
        }
    }

    public function NewProductsAllocationBase(Request $request, Company $company)
    {
        $sales_forecast = SalesForecast::company()->first();
        $allocations_setting = SecondAllocationSetting::company()->first();
        $allocation_base = $allocations_setting->allocation_base;
        $hasNewProductsItems  =getNumberOfProductsItems($company->id) ;
		if (($request->isMethod('POST')|| (!$allocations_setting->add_new_items && !$hasNewProductsItems) 
			// || (! $hasNewProductsItems 
			// && ! $allocations_setting->number_of_items
			// )
		) ) 
     {
    //  if (($request->isMethod('POST') || (! $hasNewProductsItems && ! $allocations_setting->number_of_items) )){

            foreach ((array )$request->allocation_base_data as $product => $data) {
                $total = array_sum($this->finalTotal($data));
                if ($total != 100) {

                    $validation['percentages_total'] = 'required';
                }
            }
            $validation['new_allocation_base_items.*'] = 'required';
            $request->validate(@$validation, [
                'percentages_total.required' => 'Total Percentages Must be 100%'
            ]);

            $allocation_base_data = $request->allocation_base_data;
            Cache::forever(getCacheKeyForSecondAllocationReport($company->id), ['allocation_base_data'=>$allocation_base_data , 'new_allocation_base_items'=>$request->new_allocation_base_items]);

            foreach ((array)$allocation_base_data as $product_item_name => $item_data) {
                foreach ($item_data as $base => $value) {
                    if (strstr($base, 'new_item') !== false) {
                        $index = substr($base, strpos($base, "new_item_") + 9);
                        $name_of_new_allocation_base = $request->new_allocation_base_items[$index];
                        $allocation_base_data[$product_item_name][$name_of_new_allocation_base] = array_merge($allocation_base_data[$product_item_name][$base] , ['actual_value'=>$allocation_base_data[$product_item_name][$base]['new']/100 * $request->totalsss]);
                        unset($allocation_base_data[$product_item_name][$base]);
                    }
                }
            }
            SecondNewProductAllocationBase::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'allocation_base' => $allocation_base,
                    'allocation_base_data' => $allocation_base_data,
                    'new_allocation_bases_names' => $request->new_allocation_base_items,
                ]
            );


            $allocations_base_row = SecondNewProductAllocationBase::company()->first();
            return redirect()->route('second.existing.products.allocations', $company);
        }
        $allocations_base_row = SecondNewProductAllocationBase::company()->first();

        $product_seasonality = ProductSeasonality::company()->get();
        $allocation_bases_items =   SalesGathering::company()
            ->whereNotNull($allocation_base)
            ->where($allocation_base, '!=', '')
            ->groupBy($allocation_base)
            ->get()
            ->pluck($allocation_base)
            ->toArray();
        $allocation_bases_items = array_fill_keys($allocation_bases_items, 'existing');
        if ($allocations_setting->add_new_items == 1) {
            for ($item = 0; $item < $allocations_setting->number_of_items; $item++) {
                $allocation_bases_items['new_item_' . $item] = 'new';
            }
        }
        return view('client_view.forecast.second_new_products_allocation_base', compact(
            'company',
            'sales_forecast',
            'allocation_bases_items',
            'product_seasonality',
            'allocation_base',
            'allocations_base_row',
            'allocations_setting'
        ));
    }

    public function existingProductsAllocationBase(Request $request, Company $company)
    {
		#:fixed me		
        $allocations_setting = SecondAllocationSetting::company()->first();

        $allocation_base = $allocations_setting->allocation_base;


        // Saving Existing Percentages
        if ($request->isMethod('POST')) {
            $use_modified_targets = ($request->use_modified_targets ?? 0);
            $validation['percentages_total'] = $use_modified_targets == 1 && (array_sum($request->modify_sales_target) != 100) ? 'required' : '';
            $request->validate(@$validation, [
                'percentages_total.required' => 'Total Modified Sales Percentages Must be 100%'
            ]);
            SecondExistingProductAllocationBase::updateOrCreate(
                ['company_id' => $company->id],

                [
                    'existing_products_target' => $request->existing_products_target,
                    'total_existing_target' => $request->total_existing_target,
                    'allocation_base_percentages' => $request->modify_sales_target ?? [],
                    'use_modified_targets' => ($request->use_modified_targets ?? 0),
                    'allocation_base' => $allocation_base
                ]
            );

            return redirect()->route('second.new.product.seasonality', $company);
        }
        $allocations_base_row = SecondNewProductAllocationBase::company()->first();
        $existing_allocations_base = SecondExistingProductAllocationBase::company()->first();

        $sales_forecast = SalesForecast::company()->first();
        $product_seasonality = ProductSeasonality::company()->get();

        $base_name = str_replace('_', ' ', ucwords($allocation_base));

        // Fetching Data of base elements associated with their percentages from all products
        $sales_targets_values = [];
        // $percentages = [];
        foreach ((array)$allocations_base_row->allocation_base_data as $product_item_name => $item_data) {
            $product = $product_seasonality->where('name', $product_item_name)->first();
            $sales_target_value = ($product->sales_target_value ?? 0);

            foreach ($item_data as $base => $value) {
                $type = array_key_first($value);

                $key = $base . ' [ ' . ucfirst($type) . ' ' . $base_name . ' ] ';
                $sales_targets_values[$key] = ($sales_targets_values[$key] ?? 0) + (($value[$type] / 100) * ($sales_target_value));
                // $percentages[$key] = ($percentages[$key] ??0) + ($value[$type])  ;

            }
        }
        arsort($sales_targets_values);


        $breakdown_base_data = [];
        $last_3_years_breakdown_base_data = [];
        $sales_targets = [];
        $breakdown = $allocations_setting->breakdown;

        $request['type'] = $allocations_setting->allocation_base;
        $request['start_date'] = $sales_forecast->previous_year . '-01-01';
        $request['end_date'] = $sales_forecast->previous_year . '-12-31';
        if ($allocations_setting->breakdown == 'last_3_years') {
            $request['start_date'] = ($sales_forecast->previous_year - 2) . '-01-01';
            $request['end_date'] = $sales_forecast->previous_year . '-12-31';
        }
        $breakdown_base_data = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'array');
        if ($allocations_setting->breakdown == 'new_breakdown_quarterly') {
            $total_monthly_targets  = (new SalesForecastReport)->productsAllocations($company, $request, 'array');

            $count = 1;
            $sales_targets = [];
            $total_quarter = 0;
            foreach ($total_monthly_targets as $key => $value) {
                if ($count % 3 == 0) {
                    $total_quarter += $value;
                    $sales_targets['Quarter ' . $count / 3] = $total_quarter;
                    $total_quarter = 0;
                } else {
                    $total_quarter += $value;
                }

                $count++;
            }
        }


        $new_allocation_bases_names = collect($allocations_base_row->new_allocation_bases_names)->flatMap(function ($item) use (&$breakdown_base_data) {
            $new_value = [
                "item" => $item,
                "Sales Value" => 0,
            ];
            return array_push($breakdown_base_data, $new_value);
        });
        return view('client_view.forecast.second_existing_products_allocation_base', compact(
            'company',
            'sales_forecast',
            'existing_allocations_base',
            'allocation_base',
            'allocations_base_row',
            'sales_targets_values',
            'breakdown_base_data',
            'allocation_base',
            'allocations_setting',
            'breakdown',
            'sales_targets',
            'new_allocation_bases_names'
        ));
    }

    public function allocations(Request $request, Company $company)
    {
        $sales_forecast = SalesForecast::company()->first();
        $allocation_base = '';
        $breakdown_base_data = [];
        $last_3_years_breakdown_base_data = [];
        $sales_targets = [];
        $breakdown = $request->breakdown;
        if ($request->isMethod('POST')) {
            $allocation_base = $request->allocation_base;
            $request['type'] = $request->allocation_base;
            $request['start_date'] = $sales_forecast->previous_year . '-01-01';
            $request['end_date'] = $sales_forecast->previous_year . '-12-31';
            if ($request->breakdown == 'last_3_years') {
                $request['start_date'] = ($sales_forecast->previous_year - 2) . '-01-01';
                $request['end_date'] = $sales_forecast->previous_year . '-12-31';
            }
            $breakdown_base_data = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'array');
            if ($request->breakdown == 'new_breakdown_quarterly') {
                $total_monthly_targets  = (new SalesForecastReport)->productsAllocations($company, $request, 'array');
                $count = 1;
                $sales_targets = [];
                $total_quarter = 0;
                foreach ($total_monthly_targets as $key => $value) {
                    if ($count % 3 == 0) {
                        $total_quarter += $value;
                        $sales_targets['Quarter ' . $count / 3] = $total_quarter;
                        $total_quarter = 0;
                    } else {
                        $total_quarter += $value;
                    }

                    $count++;
                }
            }
        } else {
        }
        return view('client_view.forecast.second_allocations', compact(
            'company',
            'sales_forecast',
            'breakdown_base_data',
            'allocation_base',
            'breakdown',
            'sales_targets'
        ));
    }

    public function NewProductsSeasonality(Request $request, Company $company, $result = 'view')
    {
        if ($request->isMethod('POST')) {
            return redirect()->route('collection.settings', $company);
        }
        $has_product_item = $this->fields($company);
        $type = ($has_product_item === true) ? 'product_item' : 'product_or_service';

        $new_products_allocations = SecondNewProductAllocationBase::company()->first();
        $products_seasonality = ProductSeasonality::company()->get();

        $sales_forecast = SalesForecast::company()->first();
        $allocations_setting = SecondAllocationSetting::company()->first();
        $allocation_base_data = isset($new_products_allocations->allocation_base_data) ? collect($new_products_allocations->allocation_base_data)->map(function ($data, $item) {
            return collect($data)->map(function ($sub_data, $sub_item) use ($item) {
                return Arr::first($sub_data);
            });
        })->toArray() : [];

        $allocation_data_per_allocation_base = [];
		// allocation_data_total

        foreach ($allocation_base_data as $product_name => $base_items) {
            $row = $products_seasonality->where('name', $product_name)->first();

            $sales_target_value = $row->sales_target_value;

            $seasonality = $row->seasonality;

            $seasonality_data = $row->seasonality_data;

            foreach ($base_items as $base => $percentage) {

                $percentage = $percentage ?? 0;
                $allocation_data_per_allocation_base[$base][$product_name]['target'] = $sales_target_value * ($percentage / 100);
                $allocation_data_per_allocation_base[$base][$product_name]['seasonality'] = $seasonality;
                $allocation_data_per_allocation_base[$base][$product_name]['seasonality_data'] = $seasonality_data;
            }
        }

        $monthly_dates = [];
        $counter = 1;
        for ($month = 0; $month < 12; $month++) {
            $date = $this->dateCalc($sales_forecast->start_date, $month);
            $monthly_dates[$date] = '';
            $counter++;
        }



        $year = date('Y', strtotime($sales_forecast->start_date));
        $allocation_data = [];
        $allocation_data_total = [];
		
        if (count($products_seasonality) > 0) {
            foreach ($allocation_data_per_allocation_base as $base_item => $products_data) {
                foreach ($products_data as $product_name => $product_data) {
                    $sales_target_value = $product_data['target'];
                    $seasonality = $product_data['seasonality'];
                    $seasonality_data = $product_data['seasonality_data'];


                    $forecast_seasonality_data = $this->sorting($seasonality_data);

                    $allocation_data[$base_item][$product_name] = (new SalesForecastReport)->seasonalityFun(
                        $seasonality,
                        $forecast_seasonality_data,
                        $monthly_dates,
                        $sales_target_value,
                        $year
                    );


                }
            }
foreach ($allocation_data as $base => $data) {
	foreach($data as $item=>$arr) // this foreach add by me
	{
		$allocation_data_total[$base][$item] = $this->finalTotal($data);
	}



            }

            arsort($allocation_data_total);
            $allocation_data_total['Total'] = $this->finalTotal($allocation_data);
        }
		
		// $total = $allocation_data_total['Total']??[]; 
		// unset($allocation_data_total['Total']);
		// $allocation_data_total['Total'] = $total;
		// foreach($allocation_data_total as $key => $values){
		// 	if($key != 'Total'){
		// 		$allocation_data_total[$key] = $allocation_data[$key];
		// 	}
		// }
		// $allocation_data_total['Total'] = $total ;
        // Existing

        $existing_product_data = $this->existingProducts($request, $company, $type);
        $year = date('Y', strtotime($sales_forecast->start_date));
        if ($result == 'view') {
            return view('client_view.forecast.second_new_product_seasonality', compact(
                'new_products_allocations',
                'allocation_data_total',
				'allocation_data',
                'products_seasonality',
                'company',
                'existing_product_data',
                'year'
            ));
        } else {

            /* by salah */
            $total_sales_targets = [];
			
            foreach ($allocation_data_total as $base => $base_data) {
                foreach ($base_data as $item => $values) {
                    if(\is_array($values))
                    {
                         foreach($values as $date=>$value)
                            {
                                    $month = date('F', strtotime(('01-' . $date)));

                            $full_date = date('d-m-Y', strtotime(('01-' . $date)));
                            // $total_sales_targets[$base][][$full_date] = ($existing_product_data[$base][$month] ?? 0) + (is_array($value) ? array_sum($value) : $value); // by salah
                            $total_sales_targets[$base][$item][$full_date] = ($existing_product_data[$base][$month] ?? 0) + $value;

                            }
                    }

                }
            }
            /* end by salah */


            //  foreach ($allocation_data_total as $base => $base_data) {
            //     foreach ($base_data as $date => $value) {

            //         $month = date('F', strtotime(('01-' . $date)));
            //         $full_date = date('d-m-Y', strtotime(('01-' . $date)));
            //         $total_sales_targets[$base][$full_date] = ($existing_product_data[$base][$month] ?? 0) + $value;
            //     }
            // }

            // unset($total_sales_targets['Total']);

             if(!$total_sales_targets)
            {
                unset($existing_product_data['Total']);
                return $existing_product_data ;

            }
			return get_total_with_preserve_key($total_sales_targets);
        }
    }

    public function existingProducts($request, $company, $type)
    {
            $start_date = null ;
        $end_date = null ;
        $modified_seasonality = ModifiedSeasonality::company()->first();
        $allocation_setting = SecondAllocationSetting::company()->first();
        $sales_forecast = SalesForecast::company()->first();
        // Top 50 + Chosen Others => Product_items
        $original_seasonality = $modified_seasonality->original_seasonality;
        unset($original_seasonality['Others']);
        $products_items = array_keys($original_seasonality);
        $allocation_base = $allocation_setting->allocation_base;
        $request['type'] = $allocation_base;
        if ($allocation_setting->breakdown == "last_3_years") {

            $start_date  = ($sales_forecast->previous_year - 2) . '-01-01';
            $end_date    = $sales_forecast->previous_year . '-12-31';
        } elseif ($allocation_setting->breakdown == "previous_year") {
            $start_date  = $sales_forecast->previous_year . '-01-01';
            $end_date    = $sales_forecast->previous_year . '-12-31';
        }



        $existing_product_allocation_base =  SecondExistingProductAllocationBase::company()->first();
        $existing_sales_targets  = $existing_product_allocation_base->existing_products_target??[] ;

        $sales_targets=[];
        if ($existing_product_allocation_base->use_modified_targets == 1) {
            // $existing_products_items_sales_targets_total = array_sum($existing_sales_targets);
            $modified_allocation_base_percentages = $existing_product_allocation_base->allocation_base_percentages;
            foreach ($modified_allocation_base_percentages as $base_name => $percentage) {

                $sales_targets[$base_name] = (($percentage??0)/100) * ($existing_product_allocation_base->total_existing_target??0) ;
            }
        }else {
            $sales_targets = $existing_sales_targets;
        }
        $modified_seasonality = ModifiedSeasonality::company()->first();
        $seasonality = $modified_seasonality->use_modified_seasonality == 1 ? $modified_seasonality->modified_seasonality       : $modified_seasonality->original_seasonality;

        $modified_targets = ModifiedTarget::company()->first();

        $use_modified_targets = $modified_targets->use_modified_targets;
        $products_modified_targets = $modified_targets->products_modified_targets;
        // Others Index

        $input = preg_quote('Others', '~'); // don't forget to quote input string!

        $others_name_index = preg_grep('~' . $input . '~', array_keys($modified_targets->sales_targets_percentages ?: []));
        $others_name_index = Arr::first($others_name_index);

        // Check if the calculation on sales value OR net sales
        $exportableFields  = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');
        $db_names = array_keys($exportableFields);
        $used_field = (false !== $found = array_search('sales_value',$db_names)) ? 'sales_value' : 'net_sales_value';


        // Existence of allocation base
        $existence_of_allocation_base = (false !== $found = array_search($allocation_base,$db_names)) ? true : false;


        $product_items_percentages =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$used_field." ," . $allocation_base .",id,(CASE WHEN ".$used_field." < 0 THEN 0 ELSE ".$used_field." END) as ".$used_field.",".$type."
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."' AND date between '".$start_date."' and '".$end_date."')
                ORDER BY id "
                )))
        ->where($allocation_base, '!=', '')
        ->groupBy($allocation_base)
        ->map(function($item,$name)use($type,$used_field,$products_items,$sales_targets,$use_modified_targets,$products_modified_targets,$others_name_index){
            $total = $item->sum($used_field);
            $sales_target = ($sales_targets[$name]??0);
            //1- product_items

            $product_items_top = $item->whereIn($type,$products_items)->groupBy($type)->map(function($sub_item,$product_item) use($total,$used_field,$sales_target,$use_modified_targets,$products_modified_targets){
                if(($use_modified_targets == 1 && $products_modified_targets[$product_item]['percentage'] !== null && $products_modified_targets[$product_item]['percentage'] !== 0) || ($use_modified_targets == 0) ){
                    $sales_value = $sub_item->sum($used_field);

                    $product_item_percentage = ($total == 0 ) ? 0 :  ((($sales_value??0)/$total));
                    return  ($product_item_percentage * $sales_target);
                }else{
                    return 0;
                }
            });
            //2- Others
            $others = $item->whereNotIn($type,$products_items)->groupBy($type)->map(function($sub_item) use($used_field)  {
                return $sub_item->sum($used_field) ;
            });

            $others_percentage = ($total == 0 ) ? 0 :  ( array_sum($others->toArray()) /$total ) ;


            if(isset( $products_modified_targets[$others_name_index]) && ($use_modified_targets == 1 && $products_modified_targets[$others_name_index]['percentage'] !== null && $products_modified_targets[$others_name_index]['percentage'] !== 0) || ($use_modified_targets == 0)){
                $product_items_top["Others " .count($others)] = $others_percentage *$sales_target;

            }else{
                $product_items_top["Others " .count($others)] = 0;
            }
            return $product_items_top;
        })
        ->toArray();



        if ($use_modified_targets == 1) {
            $result = [];
            foreach ($product_items_percentages as $base_name => $base_products_items) {
                $total_base_products_items = array_sum($base_products_items);
                foreach ($base_products_items as $product_item => $value) {
                    $percentage = ($total_base_products_items == 0) ? $total_base_products_items : $value / $total_base_products_items;
                    $result[$base_name][$product_item] = ($sales_targets[$base_name] ?? 0) * $percentage;
                }
            }
            $product_items_percentages = $result;
        }



        $allocation = SecondNewProductAllocationBase::company()->first() ;

        $new_allocation_bases_names = $allocation ? $allocation->new_allocation_bases_names :[] ;

        if (isset($new_allocation_bases_names) && count($new_allocation_bases_names) > 0) {

            $target_percentages = $modified_targets->sales_targets_percentages;

            foreach ($new_allocation_bases_names as $key => $base_name) {
                $sales_target_per_new_base = ($sales_targets[$base_name] ?? 0);
                foreach ((array)$target_percentages as $product_name => $percentage) {
                    $product_items_percentages[$base_name][$product_name] =  $percentage * $sales_target_per_new_base;
                }
            }
        }


        // The Summation of products items for each base

        $existing_product_data = [];
        foreach ($product_items_percentages as $base_name => $products_items) {
            $existing_product_per_allocation_base = [];

            if(count($products_items) > 0)
            {
                 foreach ($products_items as $product_item_name => $product_value) {
                $name = strstr($product_item_name, 'Others') ? 'Others' : $product_item_name;
                $product_seasonality = $seasonality[$name] ?? [];
                $existing_product_per_allocation_base[$product_item_name] = $this->operationAmongArrayAndNumber($product_seasonality, $product_value, 'multiply');
                }
            }
            $total = $this->finalTotal($existing_product_per_allocation_base);
            $total  = $this->sorting($total);
            $existing_product_data[$base_name] = $total;
        }
        $existing_product_data['Total'] = $this->finalTotal($existing_product_data);

        return $existing_product_data;
    }
}
