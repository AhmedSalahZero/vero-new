<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessUnitsAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {

        if (request()->route()->named('businessUnits.zones.analysis')) {
            $type = 'zone';
            $view_name = 'Business Units Against Zones Trend Analysis' ;
        } elseif (request()->route()->named('businessUnits.customers.analysis')) {
            $type = 'customer_name';
            $view_name = 'Business Units Against Customers Trend Analysis' ;
        }
		elseif (request()->route()->named('businessUnits.countries.analysis')) {
            $type = 'country';
            $view_name = 'Business Units Against Countries Trend Analysis' ;
        }
		elseif (request()->route()->named('businessUnits.categories.analysis')) {
            $type  = 'category';
            $view_name = 'Business Units Against Categories Trend Analysis' ;
        }elseif (request()->route()->named('businessUnits.products.analysis')) {
            $type  = 'product_or_service';
            $view_name = 'Business Units Against Products / Services Trend Analysis' ;
        }elseif (request()->route()->named('businessUnits.principles.analysis')) {
            $type  = 'principle';
            $view_name = 'Business Units Against Principles Trend Analysis' ;
        }elseif (request()->route()->named('businessUnits.Items.analysis')) {
            $type  = 'product_item';
            $view_name = 'Business Units Against Products Items Trend Analysis' ;
        }elseif (request()->route()->named('businessUnits.salesPersons.analysis')) {
            $type  = 'sales_person';
            $view_name = 'Business Units Against Sales Persons Trend Analysis' ;
        }elseif (request()->route()->named('businessUnits.salesDiscount.analysis')) {
            $type  = 'quantity_discount';
            $view_name = 'Business Units Against Sales Discount Trend Analysis' ;
        }elseif (request()->route()->named('businessUnits.branches.analysis')) {
            $type  = 'branch';
            $view_name = 'Business Units Against Branches Trend Analysis' ;
        }elseif (request()->route()->named('businessUnits.salesChannels.analysis')) {
            $type  = 'sales_channel';
            $view_name = 'Business Units Against Sales Channels Trend Analysis' ;
        }elseif (request()->route()->named('businessUnits.products.averagePrices')) {
            $type  = 'averagePrices';
            $view_name = 'Business Units Products / Services Average Prices' ;
        }
        elseif (request()->route()->named('businessUnits.Items.averagePrices')) {
            $type  = 'averagePricesProductItems';
            $view_name = 'Business Units Items Average Prices' ;
        }
		elseif (request()->route()->named('businessUnits.day.analysis')) {
            $type  = 'day_name';
            $view_name = 'Business Units Against Days Trend Analysis' ;
		}
        $name_of_selector_label = str_replace(['Business Units Against ' ,' Trend Analysis'],'',$view_name);

        if ($type == 'averagePrices') {
            $name_of_selector_label = 'Products / Services';
        } elseif($type  == 'averagePricesProductItems') {
            $name_of_selector_label = 'Products Items';
        }
        // $name_of_selector_label = ($type == 'averagePrices') ? 'Products / Services' : str_replace(['Business Units Against ' ,' Trend Analysis'],'',$view_name);
        return view('client_view.reports.sales_gathering_analysis.businessUnits_analysis_form', compact('company','name_of_selector_label','type','view_name'));
    }
    public function BusinessUnitsSalesAnalysisIndex(Company $company)
    {
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
        return view('client_view.reports.sales_gathering_analysis.businessUnits_sales_form', compact('company', 'selected_fields'));
    }
    public function result(Request $request, Company $company,$result='view' , $secondReport = true )
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

        $report_data =[];
        $report_data_quantity =[];
        $growth_rate_data =[];
        $final_report_total =[];
        $businessUnits_names = [];
		
        $businessUnits = is_array(json_decode(($request->businessUnits[0]))) ? json_decode(($request->businessUnits[0])) :$request->businessUnits ;
        $type = $request->type;
        $view_name = $request->view_name;
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'net_sales_value' : 'quantity';
        $name_of_report_item  = ($result=='view') ? 'Sales Values' : 'Avg. Prices';
        foreach ($businessUnits as  $businessUnitName) {
            if ($result == 'view') {
                $businessUnits_data =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$data_type." ,business_unit," . $type ."
                    FROM sales_gathering
                    WHERE ( company_id = '".$company->id."'AND business_unit = '".$businessUnitName."' AND date between '".$request->start_date."' and '".$request->end_date."')
                    ORDER BY id "
                    )))->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){

                            return $sub_item->sum($data_type);
                        });
                    })->toArray();
            }else{
                $businessUnits_data = DB::table('sales_gathering')
                    ->where('company_id',$company->id)
                    ->where('business_unit', $businessUnitName)
                    ->whereNotNull($type)
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,
                    (IFNULL('.$data_type.',0) ) as '.$data_type.' ,business_unit,' . $type)
                    ->get()
                    ->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){
                            return  $sub_item->sum($data_type);
                        });
                    })->toArray();

            }

             $businessUnits_data_quantity = DB::table('sales_gathering')
                    ->where('company_id',$company->id)
                    ->where('business_unit', $businessUnitName)
                    ->whereNotNull($type)
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,
                    (IFNULL('.$data_type.',0) ) as '.$data_type.' ,IFNULL(quantity_bonus,0) quantity_bonus , IFNULL(quantity,0) quantity,business_unit,' . $type)
                    ->get()
                    ->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){
                            return ($sub_item->sum('quantity_bonus') + $sub_item->sum('quantity') );
                        });
                    })->toArray();
            
              
               



            foreach (($request->sales_channels??[]) as $businessUnit_key => $businessUnit) {

                $years = [];

                $data_per_main_item = $businessUnits_data[$businessUnit]??[];
                if (count(($data_per_main_item))>0 ) {

                    // Data & Growth Rate Per Sales Channel
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                        $years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);

                    $report_data[$businessUnitName][$businessUnit][$name_of_report_item] = $data_per_main_item;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data[$businessUnitName][$businessUnit], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
                    $report_data[$businessUnitName][$businessUnit] = $interval_data['data_intervals'][$request->interval] ?? [];

                    $report_data[$businessUnitName]['Total']  = $this->finalTotal([($report_data[$businessUnitName]['Total']  ?? []) ,($report_data[$businessUnitName][$businessUnit][$name_of_report_item]??[]) ]);
                    $report_data[$businessUnitName][$businessUnit]['Growth Rate %'] = $this->growthRate(($report_data[$businessUnitName][$businessUnit][$name_of_report_item] ?? []));



                
            }
            }


            if($result =='array'){
            
                 foreach (($request->sales_channels??[]) as $businessUnit_key => $businessUnit) {

                $years = [];

                $data_per_main_item = $businessUnits_data_quantity[$businessUnit]??[];
                if (count(($data_per_main_item))>0 ) {

                    // Data & Growth Rate Per Sales Channel
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                        $years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);

                    $report_data_quantity[$businessUnitName][$businessUnit][$name_of_report_item] = $data_per_main_item;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data_quantity[$businessUnitName][$businessUnit], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
                    $report_data_quantity[$businessUnitName][$businessUnit] = $interval_data['data_intervals'][$request->interval] ?? [];

                    $report_data_quantity[$businessUnitName]['Total']  = $this->finalTotal([($report_data_quantity[$businessUnitName]['Total']  ?? []) ,($report_data_quantity[$businessUnitName][$businessUnit][$name_of_report_item]??[]) ]);
                    $report_data_quantity[$businessUnitName][$businessUnit]['Growth Rate %'] = $this->growthRate(($report_data_quantity[$businessUnitName][$businessUnit][$name_of_report_item] ?? []));



                
            }
                 }



                  foreach($report_data as $reportType=>$dates){
                                // Baby 20

                      if($reportType == $businessUnitName)
                      {
                         foreach($dates as $dateName=>$items){
                    if($dateName != 'Total')
                    {
                                                //Avg. Prices
                    foreach($items as $itemKey=> $values){
                        if($itemKey == 'Avg. Prices'){
                            foreach($values as $datee => $dateVal){
                            $report_data[$reportType][$dateName][$itemKey][$datee] =  
                            $report_data_quantity[$reportType][$dateName][$itemKey][$datee] ?
                            $report_data[$reportType][$dateName][$itemKey][$datee] / $report_data_quantity[$reportType][$dateName][$itemKey][$datee]
                            : 0 ; 

                            $report_data[$reportType]['Totals'][$datee] = $report_data[$reportType][$dateName][$itemKey][$datee] + ($report_data[$reportType]['Totals'][$datee] ??0);
                          
                            
                            
                            $report_data[$reportType]['Total'][$datee] = $report_data[$reportType]['Totals'][$datee];
                            
                        }
                        
                        
                        }

                        elseif($itemKey == 'Growth Rate %'){
                            foreach($values as $datee => $dateVal){
                                $report_data[$reportType][$dateName]['Avg. Prices'][$datee];
                                $keys = array_flip(array_keys($report_data[$reportType][$dateName]['Avg. Prices']));
                                $values = array_values($report_data[$reportType][$dateName]['Avg. Prices']);
                                $previousValue = isset($values[$keys[$datee]-1]) ? $values[$keys[$datee]-1] : 0 ;
                          
                            
                                $report_data[$reportType][$dateName][$itemKey][$datee] =  $previousValue ? (($report_data[$reportType][$dateName]['Avg. Prices'][$datee] - $previousValue  )/ $previousValue)*100 : 0;
                          
                            }
                        }
                        
                        
                        
                        
                    }   
                    }
                
                }
                   
                      }

                             
               
            }
            

            
            
            }


            
            // Total & Growth Rate Per Zone
            $final_report_total = $this->finalTotal( [($report_data[$businessUnitName]['Total']??[]) , ($final_report_total??[]) ]);
            $report_data[$businessUnitName]['Growth Rate %'] =  $this->growthRate(($report_data[$businessUnitName]['Total']??[]));
            $businessUnits_names[] = (str_replace( ' ','_', $businessUnitName));
            }



              foreach($report_data as $r=>$d){
            unset($report_data[$r]['Totals']);
        }
        

        // Total Sales Channel & Growth Rate


        $report_data['Total'] = $final_report_total;
        $report_data['Growth Rate %']=  $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);
        //  $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);
         $Items_names = $businessUnits_names ;
         $report_view = getComparingReportForAnalysis($request , $report_data , $secondReport , $company , $dates , $view_name , $Items_names , 'business_unit' );

        if($report_view instanceof View)
        {
            return $report_view ; 
        }

        if($request->report_type =='comparing')
        {
             return [
                 'report_data'=>$report_data ,
                 'dates'=>$dates ,
                 'full_date' =>Carbon::make($request->start_date)->format('d M Y') .' '.__('To').' '.Carbon::make($request->end_date)->format('d M Y') 
             ];
        }
        if ($result =='view') {
            return view('client_view.reports.sales_gathering_analysis.businessUnits_analysis_report',compact('company','name_of_report_item','view_name','businessUnits_names','dates','report_data',));
        }else {
            return [ 'report_data'=>$report_data,'view_name'=>$view_name,'names'=> $businessUnits_names];
        }

    }

    public function resultForSalesDiscount(Request $request, Company $company)
    {

        $report_data =[];
        $final_report_data =[];
        $growth_rate_data =[];
        $zones_names = [];
        $sales_values = [];
        $sales_years = [];
        $zones = is_array(json_decode(($request->businessUnits[0]))) ? json_decode(($request->businessUnits[0])) :$request->businessUnits ;
        $type = $request->type;
        $view_name = $request->view_name;
        $zones_discount = [];


        $fields ='';
        foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
            $fields .= $sales_discount_field .',';
        }


        foreach ($zones as  $zone) {

            $sales =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields ." business_unit
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND business_unit = '".$zone."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id"
            )))->groupBy('gr_date');
            $sales_values_per_zone[$zone] = $sales->map(function($sub_item){
                                    return $sub_item->sum('sales_value');
                                })->toArray();



            foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
                $zones_discount = $sales->map(function($sub_item) use($sales_discount_field){
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


                    $final_report_data[$zone]['Total']  = $this->finalTotal([($final_report_data[$zone]['Total']  ?? []) ,($final_report_data[$zone][$sales_discount_field]['Values']??[]) ]);






                    $final_report_data['Total'] = $this->finalTotal([($final_report_data['Total'] ?? []), (($final_report_data[$zone][$sales_discount_field]['Values']??[]))]);


                    $final_report_data[$zone][$sales_discount_field]['Perc.% / Sales'] = $this->operationAmongTwoArrays(($final_report_data[$zone][$sales_discount_field]['Values']??[]), ($sales_values[$zone]??[]));




                }
            }
            $zones_names[] = (str_replace( ' ','_', $zone));
        }

        $sales_values = $this->finalTotal([$sales_values??[]]);
        $total = $final_report_data['Total'];
        unset($final_report_data['Total']);
        $final_report_data['Total'] = $total;
        $final_report_data['Discount % / Total Sales'] = $this->operationAmongTwoArrays($final_report_data['Total'],$sales_values);

        // Total Zones & Growth Rate

        $report_data = $final_report_data;

        $dates = array_keys($report_data['Total']);
//  $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);
        $type_name = 'Business Units';
        return view('client_view.reports.sales_gathering_analysis.sales_discounts_analysis_report',compact('company','view_name','zones_names','dates','report_data','type_name'));

    }

    public function BusinessUnitsSalesAnalysisResult(Request $request, Company $company , $array = false )
    {
        $dimension = $request->report_type;

        $report_data =[];
        $growth_rate_data =[];
        $businessUnits = is_array(json_decode(($request->businessUnits[0]))) ? json_decode(($request->businessUnits[0])) :$request->businessUnits ;

        foreach ($businessUnits as  $businessUnit) {

            $businessUnits_data =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,business_unit
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND business_unit = '".$businessUnit."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id "
                )))->groupBy('gr_date')->map(function($item){
                    return $item->sum('net_sales_value');
                })->toArray();

            $interval_data_per_item = [];
            $years = [];

            array_walk($businessUnits_data, function ($val, $date) use (&$years) {
                $years[] = date('Y', strtotime($date));
            });
            $years = array_unique($years);
            $report_data[$businessUnit] = $businessUnits_data;
            $interval_data_per_item[$businessUnit] = $businessUnits_data;
            $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$interval_data_per_item, $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);

            $report_data[$businessUnit] = $interval_data['data_intervals'][$request->interval][$businessUnit] ?? [];
            $growth_rate_data[$businessUnit] = $this->growthRate($report_data[$businessUnit]);



        }

        $total_businessUnits = $this->finalTotal($report_data);
        $total_businessUnits_growth_rates =  $this->growthRate($total_businessUnits);
        $final_report_data = [];
        $businessUnits_names =[];
        foreach ($businessUnits as  $businessUnit) {
            $final_report_data[$businessUnit]['Sales Values'] = $report_data[$businessUnit];
            $final_report_data[$businessUnit]['Growth Rate %'] = $growth_rate_data[$businessUnit];
            $businessUnits_names[] = (str_replace( ' ','_', $businessUnit));
        }

        if($array)
        {
            return $report_data ;
        }
		
		$dates = array_keys($total_businessUnits ?? []); 
		
		$final_report_data = HArr::getKeysSortedDescByKey($final_report_data,'Sales Values');

        return view('client_view.reports.sales_gathering_analysis.businessUnits_sales_report',compact('company','businessUnits_names','total_businessUnits_growth_rates','final_report_data','total_businessUnits','dates'));

    }
    public function growthRate($data)
    {

        $prev_month = 0;
        $final_data = [];
        foreach ($data as $date => $value) {
            $prev_month = (round($prev_month));
            if ($prev_month <= 0 && $value<=0) {
                $final_data[$date] = 0 ;
            }if ($prev_month <  0 && $value >= 0) {
                $final_data[$date] =  ((($value - $prev_month) / $prev_month) * 100)*(-1);
            }else{

                $final_data[$date] = $prev_month != 0 ? (($value - $prev_month) / $prev_month) * 100 : 0;
            }
            $prev_month = $value;
        }
        return $final_data;
    }


}
