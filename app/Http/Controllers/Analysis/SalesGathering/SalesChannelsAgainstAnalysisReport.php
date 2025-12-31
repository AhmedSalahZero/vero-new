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

class SalesChannelsAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {
        if (request()->route()->named('salesChannels.zones.analysis')) {
            $type = 'zone';
            $view_name = 'Sales Channels Against Zones Trend Analysis' ;
        } elseif (request()->route()->named('salesChannels.customers.analysis')) {
            $type = 'customer_name';
            $view_name = 'Sales Channels Against Customers Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.categories.analysis')) {
            $type  = 'category';
            $view_name = 'Sales Channels Against Categories Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.products.analysis')) {
            $type  = 'product_or_service';
            $view_name = 'Sales Channels Against Products / Services Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.principles.analysis')) {
            $type  = 'principle';
            $view_name = 'Sales Channels Against Principles Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.Items.analysis')) {
            $type  = 'product_item';
            $view_name = 'Sales Channels Against Products Items Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.salesPersons.analysis')) {
            $type  = 'sales_person';
            $view_name = 'Sales Channels Against Sales Persons Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.salesDiscount.analysis')) {
            $type  = 'quantity_discount';
            $view_name = 'Sales Channels Against Sales Discount Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.businessSectors.analysis')) {
            $type  = 'business_sector';
            $view_name = 'Sales Channels Against Business Sectors Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.businessUnits.analysis')) {
            $type  = 'business_unit';
            $view_name = 'Sales Channels Against Business Units Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.branches.analysis')) {
            $type  = 'branch';
            $view_name = 'Sales Channels Against Branches Trend Analysis' ;
        }elseif (request()->route()->named('salesChannels.products.averagePrices')) {
            $type  = 'averagePrices';
            $view_name = 'Sales Channels Products / Services Average Prices' ;
        }elseif (request()->route()->named('salesChannels.Items.averagePrices')) {
            $type  = 'averagePricesProductItems';
            $view_name = 'Sales Channels Items Average Prices' ;
        }
			elseif (request()->route()->named('salesChannels.countries.analysis')) {
			$type  = 'country';
			$view_name = 'Sales Channel Against Countries Trend Analysis';
		}
		elseif (request()->route()->named('salesChannels.day.analysis')) {
			$type  = 'day_name';
			$view_name = 'Sales Channel Against Days Trend Analysis';
		}

        $name_of_selector_label = str_replace(['Sales Channels Against ' ,' Trend Analysis'],'',$view_name);
        if ($type == 'averagePrices') {
            $name_of_selector_label = 'Products / Services';
        } elseif($type  == 'averagePricesProductItems') {
            $name_of_selector_label = 'Products Items';
        }elseif($type == 'country'){
            $name_of_selector_label = 'Countries';
		}

        // $name_of_selector_label = ($type == 'averagePrices') ? 'Products / Services' : str_replace(['Sales Channels Against ' ,' Trend Analysis'],'',$view_name);
        return view('client_view.reports.sales_gathering_analysis.salesChannels_analysis_form', compact('company','name_of_selector_label','type','view_name'));
    }
    public function SalesChannelsSalesAnalysisIndex(Company $company)
    {
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
        return view('client_view.reports.sales_gathering_analysis.salesChannels_sales_form', compact('company', 'selected_fields'));
    }
    public function result(Request $request, Company $company,$result = 'view' , $secondReport = true)
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
		$report_data_quantity=[];
        $report_data =[];
        $growth_rate_data =[];
        $final_report_total =[];
        $sales_channels_names = [];
        $salesChannels = is_array(json_decode(($request->salesChannels[0]))) ? json_decode(($request->salesChannels[0])) :$request->salesChannels ;
        $type = $request->type;
        $view_name = $request->view_name;
        $name_of_report_item  = ($result=='view') ? 'Sales Values' : 'Avg. Prices';
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'net_sales_value' : 'quantity';
        foreach ($salesChannels as  $salesChannelName) {
            if ($result == 'view') {
                   $salesChannels_data =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$data_type." ,sales_channel," . $type ."
                    FROM sales_gathering
                    WHERE ( company_id = '".$company->id."'AND sales_channel = '".$salesChannelName."' AND date between '".$request->start_date."' and '".$request->end_date."')
                    ORDER BY id "
                    )))->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item) use($data_type){

                            return $sub_item->sum($data_type);
                        });
                    })->toArray();
               
            }else{
              

                     
                    $salesChannels_data = DB::table('sales_gathering')
                    ->where('company_id',$company->id)
                      ->when($request->has('sales_channels') , function($query) use ($request){
                        $query->whereIn('product_item',$request->get('sales_channels'));
                    })
                    ->when($request->has('products') , function($query) use ($request){
                        $query->whereIn('product_or_service',$request->get('products'));
                    })
					->when($request->has('salesChannels') , function($query) use ($request , $salesChannelName){
						   $query->whereIn('sales_channel',(array)$salesChannelName);
						})
					->when($request->has('categories') , function($query) use ($request){
                        $query->whereIn('category' , $request->get('categories'));
                    })
                    // ->where('sales_channel', $salesChannelName)
                    ->whereNotNull($type)
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,  sales_channel , '. $data_type .' , IFNULL(quantity_bonus,0) quantity_bonus , IFNULL(quantity,0) quantity , 
                     sales_channel,' . $type)
                    
                     ->get() 
					 
                    ->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type,$item){
                            return 
                            $sub_item->sum('net_sales_value'); 
                        });
                    })->toArray();
		
                    $qq = DB::table('sales_gathering')
                    ->where('company_id',$company->id)
                      ->when($request->has('sales_channels') , function($query) use ($request){
                        $query->whereIn('product_item',$request->get('sales_channels'));
                    })
                    ->when($request->has('products') , function($query) use ($request){
                        $query->whereIn('product_or_service',$request->get('products'));
                    })
                       ->when($request->has('salesChannels') , function($query) use ($request,$salesChannelName){
                        $query->whereIn('sales_channel',[$salesChannelName]);
                    })
                    ->when($request->has('categories') , function($query) use ($request){
                        $query->whereIn('category' , $request->get('categories'));
                    })

                    // ->where('sales_channel', $salesChannelName)
                    ->whereNotNull($type)
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,  sales_channel , '. $data_type .' , IFNULL(quantity_bonus,0) quantity_bonus , IFNULL(quantity,0) quantity , 
                     sales_channel,' . $type)
                    
                     ->get() 
                    ->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type,$item){
                            
                            return 
                            ($sub_item->sum('quantity_bonus') + $sub_item->sum('quantity') ) ;
                        });
                    })->toArray();
                  
            }

          

            foreach (($request->get('sales_channels',$request->get('products',[]))) as $sales_channel_key => $sales_channel) {
                $years = [];
                $data_per_main_item = $salesChannels_data[$sales_channel]??[];
                if (count(($data_per_main_item))>0 ) {
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
						$years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);

                    $report_data[$salesChannelName][$sales_channel][$name_of_report_item] = $data_per_main_item;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data[$salesChannelName][$sales_channel], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
                    $report_data[$salesChannelName][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];
                    $report_data[$salesChannelName]['Total']  = $this->finalTotal([($report_data[$salesChannelName]['Total']  ?? []) ,($report_data[$salesChannelName][$sales_channel][$name_of_report_item]??[]) ]);
                    $report_data[$salesChannelName][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data[$salesChannelName][$sales_channel][$name_of_report_item] ?? []));

                }
            }

            

            if($result == 'array'){
                 foreach (($request->get('sales_channels',$request->get('products',[]))??[]) as $sales_channel_key => $sales_channel) {
                $years_quantity = [];

                $data_per_main_item_quantity = $qq[$sales_channel]??[];
                if (count(($data_per_main_item_quantity))>0 ) {
                    array_walk($data_per_main_item_quantity, function ($val, $date) use (&$years_quantity) {
                        $years_quantity[] = date('Y', strtotime($date));
                    });
                    $years_quantity = array_unique($years_quantity);

                    $report_data_quantity[$salesChannelName][$sales_channel][$name_of_report_item] = $data_per_main_item_quantity;
                    $interval_data_quantity = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data_quantity[$salesChannelName][$sales_channel], $years_quantity, $request->interval);
                    $report_data_quantity[$salesChannelName][$sales_channel] = $interval_data_quantity['data_intervals'][$request->interval] ?? [];

                    $report_data_quantity[$salesChannelName]['Total']  = $this->finalTotal([($report_data_quantity[$salesChannelName]['Total']  ?? []) ,($report_data_quantity[$salesChannelName][$sales_channel][$name_of_report_item]??[]) ],'dates', 20);
                    $report_data_quantity[$salesChannelName][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data_quantity[$salesChannelName][$sales_channel][$name_of_report_item] ?? []));

                }
                
                
                }
                 foreach($report_data as $reportType=>$dates){

                     
                                // Baby 20
  if($reportType == $salesChannelName){
    foreach($dates as $dateName=>$items){
                    if($dateName != 'Total')
                    {
                                                //Avg. Prices
                    foreach($items as $itemKey=> $values){
                        if($itemKey == 'Avg. Prices'){
                            foreach($values as $datee => $dateVal){
                            $report_data[$reportType][$dateName][$itemKey][$datee] =  
                            isset($report_data_quantity[$reportType][$dateName][$itemKey][$datee]) && $report_data_quantity[$reportType][$dateName][$itemKey][$datee] ?
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
            $final_report_total = $this->finalTotal( [($report_data[$salesChannelName]['Total']??[]) , ($final_report_total??[]) ]);
            $report_data[$salesChannelName]['Growth Rate %'] =  $this->growthRate(($report_data[$salesChannelName]['Total']??[]));
            $sales_channels_names[] = (str_replace( ' ','_', $salesChannelName));
        }
        foreach($report_data as $r=>$d){
            unset($report_data[$r]['Totals']);
        }
        // Total Sales Channel & Growth Rate

        $report_data['Total'] = $final_report_total;
        $report_data['Growth Rate %']=  $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);
		

           $Items_names = $sales_channels_names ;
         $report_view = getComparingReportForAnalysis($request , $report_data , $secondReport , $company , $dates , $view_name , $Items_names , 'sales_channel' );

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
			return view('client_view.reports.sales_gathering_analysis.salesChannels_analysis_report',compact('company','type','name_of_report_item','view_name','sales_channels_names','dates','report_data',));
        }else {
            return [ 'report_data'=>$report_data,'view_name'=>$view_name,'names'=> $sales_channels_names];
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
        $zones = is_array(json_decode(($request->salesChannels[0]))) ? json_decode(($request->salesChannels[0])) :$request->salesChannels ;
        $type = $request->type;
        $view_name = $request->view_name;
        $zones_discount = [];


        $fields ='';
        foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
            $fields .= $sales_discount_field .',';
        }


        foreach ($zones as  $zone) {

            $sales =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields ." sales_channel
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND sales_channel = '".$zone."' AND date between '".$request->start_date."' and '".$request->end_date."')
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
        // $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);
        $type_name = 'Sales Channels';
        
        
        
        
        return view('client_view.reports.sales_gathering_analysis.sales_discounts_analysis_report',compact('company','view_name','zones_names','dates','report_data','type_name'));

    }

    public function SalesChannelsSalesAnalysisResult(Request $request, Company $company , $array=false )
    {
        $dimension = $request->report_type;

        $report_data =[];
        $growth_rate_data =[];

        $sales_channels = is_array(json_decode(($request->sales_channels[0]))) ? json_decode(($request->sales_channels[0])) :$request->sales_channels ;
        foreach ($sales_channels as  $sales_channel) {

            $sales_channels_data =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,sales_channel
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND sales_channel = '".$sales_channel."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id "
                )))->groupBy('gr_date')->map(function($item){
                    return $item->sum('net_sales_value');
                })->toArray();
         
            $interval_data_per_item = [];
            $years = [];

            if (count($sales_channels_data)>0) {

                array_walk($sales_channels_data, function ($val, $date) use (&$years) {
                    $years[] = date('Y', strtotime($date));
                });
                $years = array_unique($years);
                $report_data[$sales_channel] = $sales_channels_data;
                $interval_data_per_item[$sales_channel] = $sales_channels_data;
			
		
                $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$interval_data_per_item, $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
		
                $report_data[$sales_channel] = $interval_data['data_intervals'][$request->interval][$sales_channel] ?? [];
                $growth_rate_data[$sales_channel] = $this->growthRate($report_data[$sales_channel]);



            }
        }
	
        $total_sales_channels = $this->finalTotal($report_data);
        $total_sales_channels_growth_rates =  $this->growthRate($total_sales_channels);
        $final_report_data = [];
        $sales_channels_names =[];
        foreach ($sales_channels as  $sales_channel) {
            $final_report_data[$sales_channel]['Sales Values'] = ($report_data[$sales_channel]??[]);
            $final_report_data[$sales_channel]['Growth Rate %'] = ($growth_rate_data[$sales_channel]??[]);
            $sales_channels_names[] = (str_replace( ' ','_', $sales_channel));
        }

        if($array)
        {
            
            return $report_data;
        }
		$dates = array_keys($total_sales_channels ?? []) ;
	
		$final_report_data = HArr::getKeysSortedDescByKey($final_report_data,'Sales Values');
        return view('client_view.reports.sales_gathering_analysis.salesChannels_sales_report',compact('company','sales_channels_names','total_sales_channels_growth_rates','final_report_data','total_sales_channels','dates'));

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
    // Ajax


}
