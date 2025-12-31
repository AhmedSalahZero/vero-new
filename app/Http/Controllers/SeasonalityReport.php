<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport;
use App\Models\Company;
use App\Models\ModifiedSeasonality;
use App\Models\ModifiedTarget;
use App\Models\ProductSeasonality;
use App\Models\SalesForecast;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeasonalityReport
{
    use GeneralFunctions;
    public function modifySeasonality(Request $request, Company $company)
    {


        $sales_forecast = SalesForecast::company()->first();
        $products_seasonality = ProductSeasonality::company()->get();
        $has_product_item = (new SalesForecastReport)->fields($company);
        $type = ($has_product_item===true) ?'product_item' : 'product_or_service';

        $monthly_dates = [];
        $counter = 1;
        for ($month = 0; $month < 12; $month++) {
            $date = $this->dateCalc($sales_forecast->start_date, $month);
            $monthly_dates[$date] = '';
            if ($counter % 3 == 0) {
                $quarter_dates[$date] = '';
            }
            $counter++;
        }



        $products = SalesGathering::company()
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
			
            $products_data = collect(DB::select(DB::raw("
                        SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value,service_provider_name," . $type ."
                        FROM sales_gathering
                        WHERE ( company_id = '" . $company->id . "'AND " . $type ." IS NOT NULL  AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
                        ORDER BY id "
            )))->whereIn($type, $products);
        } elseif ($sales_forecast->seasonality == "previous_year") {

            $request['start_date']  = $sales_forecast->previous_year . '-01-01';
            $request['end_date']    = $sales_forecast->previous_year . '-12-31';
        }
        $modified_targets = ModifiedTarget::company()->first();

        $product_item_breakdown_data = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'withOthers', $products_data);
        $product_item_breakdown_data = (new SalesForecastReport)->addingOthersToData($product_item_breakdown_data, $modified_targets->others_target);

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


        if ($modified_seasonality === null || (count($product_item_breakdown_data_items) != (count(($modified_seasonality->original_seasonality ?? []) ))   )) {

            $products_items_monthly_percentage =  $this->productsItemsData($request, $company, $sales_forecast, $product_item_breakdown_data_items,$type);
            if ($modified_seasonality === null) {
                ModifiedSeasonality::create([
                    'company_id' => $company->id,
                    'original_seasonality' => $products_items_monthly_percentage,
                    'use_modified_seasonality' => 0
                ]);
            }else{
                $modified_seasonality->update([
                    'original_seasonality' => $products_items_monthly_percentage,
                ]);
                $modified_seasonality->save();
            }
        } elseif (isset($modified_seasonality) && $modified_seasonality->modified_seasonality !== null ) {

            $products_items_monthly_percentage = $modified_seasonality->modified_seasonality;
        }else{
            $products_items_monthly_percentage = $modified_seasonality->original_seasonality;
        }
        return view('client_view.forecast.modify_seasonality', compact(
                    'company',
                'monthly_dates',
                'sales_forecast',
                'products_seasonality',
                'products_items_monthly_values',
                'product_item_breakdown_data',
                'modified_targets',
                'modified_seasonality',
                'products_items_monthly_percentage'));
    }

    public function saveSeasonality(Request $request, Company $company)
    {
        $modified_seasonality_per_product = [];

        foreach ($request->modified_seasonality as $name => $seasonality) {
            $modified_seasonality_per_product[$name] = $this->operationAmongArrayAndNumber($seasonality,100);
        }

        $modified_seasonality = ModifiedSeasonality::company()->first();
        $modified_seasonality->update([
            'modified_seasonality' => $modified_seasonality_per_product,
            'use_modified_seasonality' => $request->use_modified_seasonality??0,
        ]);
        $modified_seasonality->save();

        toastr('Seasonality Updated','success');
        return redirect()->route('products.allocations',$company);
    }

    public function productsItemsData($request,$company,$sales_forecast,$product_item_breakdown_data,$type)
    {
            $last_year_start_date  = ($sales_forecast->previous_year . '-01-01');
            $last_year_end_date    = $sales_forecast->previous_year . '-12-31';

            $products = array_keys($product_item_breakdown_data);

            $mainData_data = [];
            $others = [];
            if ($sales_forecast->seasonality == "last_3_years") {
                $mainData_data =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(date),'%M') as gr_date ,id,(CASE WHEN net_sales_value < 0 THEN 0 ELSE net_sales_value END) as net_sales_value," . $type ."
                    FROM sales_gathering
                    WHERE ( company_id = '".$company->id."' AND date between '".$request->start_date."' and '".$request->end_date."')

                    ORDER BY id "
                    )))->whereIn($type,$products)
                    ->groupBy($type)->map(function($item){
                        $total = $item->sum('net_sales_value');

                        return $item->groupBy('gr_date')->map(function($sub_item) use($total){
                            $net_sales_value = $sub_item->sum('net_sales_value');
                            return ($total == 0 ) ? 0 :  ((($net_sales_value??0)/$total)) ;
                        });
                    })->toArray();
                $others =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(date),'%M') as gr_date ,id,(CASE WHEN net_sales_value < 0 THEN 0 ELSE net_sales_value END) as net_sales_value," . $type ."
                    FROM sales_gathering
                    WHERE ( company_id = '".$company->id."' AND date between '".$request->start_date."' and '".$request->end_date."')

                    ORDER BY id "
                    )))->whereNotIn($type,$products)
                       ->groupBy('gr_date')->map(function($sub_item) {
                            return  $sub_item->sum('net_sales_value'); ;

                    })->toArray();
            } elseif($sales_forecast->seasonality == "previous_year") {

                $mainData_data =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(date),'%M') as gr_date ,id,(CASE WHEN net_sales_value < 0 THEN 0 ELSE net_sales_value END) as net_sales_value," . $type ."
                    FROM sales_gathering
                    WHERE ( company_id = '".$company->id."' AND date between '".$request->start_date."' and '".$request->end_date."')

                    ORDER BY id "
                    )))->whereIn($type,$products)
                    ->groupBy($type)->map(function($item){

                        $total = $item->sum('net_sales_value');
                        return $item->groupBy('gr_date')->map(function($sub_item) use($total){
                            $net_sales_value = $sub_item->sum('net_sales_value');
                            return ($total == 0 ) ? 0 :  ((($net_sales_value??0)/$total)) ;
                        });
                    })->toArray();

                $others =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(date),'%M') as gr_date ,id,(CASE WHEN net_sales_value < 0 THEN 0 ELSE net_sales_value END) as net_sales_value
                    FROM sales_gathering
                    WHERE ( company_id = '".$company->id."' AND date between '".$request->start_date."' and '".$request->end_date."')

                    ORDER BY id "
                    )))->whereNotIn($type,$products)
                    ->groupBy('gr_date')->map(function($sub_item)  {

                        return  $sub_item->sum('net_sales_value');

                 })->toArray();
            }
            $others_total = array_sum($others);
            $mainData_data['Others'] = collect($others)->map(function($sub_item) use($others_total){
                return  ($others_total == 0 ) ? 0 :  ((($sub_item??0)/$others_total)) ;
            })->toArray();

        return $mainData_data;
    }
//

}
