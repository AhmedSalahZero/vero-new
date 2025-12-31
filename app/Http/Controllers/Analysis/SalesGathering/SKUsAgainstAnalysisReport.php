<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SKUsAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {
        if (request()->route()->named('Items.sales.analysis')) {
            $type = 'product_item';
            $view_name = 'Products Items Trend Analysis';
        } elseif (request()->route()->named('Items.zones.analysis')) {
            $type = 'zone';
            $view_name = 'Products Items Against Zones Trend Analysis';
        } elseif (request()->route()->named('Items.customers.analysis')) {
            $type = 'customer_name';
            $view_name = 'Products Items Against Customers Trend Analysis';
        } elseif (request()->route()->named('Items.salesChannels.analysis')) {
            $type  = 'sales_channel';
            $view_name = 'Products Items Against Sales Channels Trend Analysis';
        }
		elseif (request()->route()->named('Items.countries.analysis')) {
            $type  = 'country';
            $view_name = 'Products Items Against Countries Trend Analysis';
        }
        elseif (request()->route()->named('Items.salesPersons.analysis')) {
            $type  = 'sales_person';
            $view_name = 'Products Items Against Sales Persons Trend Analysis';
        } elseif (request()->route()->named('Items.salesDiscount.analysis')) {
            $type  = 'quantity_discount';
            $view_name = 'Products Items Against Sales Discount Trend Analysis';
        } elseif (request()->route()->named('Items.businessSectors.analysis')) {
            $type  = 'business_sector';
            $view_name = 'Products Items Against Business Sectors Trend Analysis';
        } elseif (request()->route()->named('Items.businessUnits.analysis')) {
            $type  = 'business_unit';
            $view_name = 'Products Items Against Business Units Trend Analysis';
        } 
		elseif (request()->route()->named('Items.branches.analysis')) {
            $type  = 'branch';
            $view_name = 'Products Items Against Branches Trend Analysis';
        }
		elseif (request()->route()->named('Items.principles.analysis')) {
            $type  = 'principle';
            $view_name = 'Products Items Against Principle Trend Analysis';
        }elseif (request()->route()->named('Items.day.analysis')) {
            $type  = 'day_name';
            $view_name = 'Products Items Against Days Trend Analysis';
        }
        $name_of_selector_label = str_replace(['Products Items Against ', ' Trend Analysis'], '', $view_name);
        return view('client_view.reports.sales_gathering_analysis.skus_analysis_form', compact('company', 'name_of_selector_label', 'type', 'view_name'));
    }
	public function viewBundlingReport(Request $request , Company $company)
	{
            $main_type = 'product_item';
            $type = 'product_item';
            $view_name = 'Bundled Product Items' ;
        return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_form', compact('company', 'view_name','type','main_type'));
	}
	
     public function CategoriesSalesAnalysisIndex(Company $company)
    {
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
        return view('client_view.reports.sales_gathering_analysis.product_items_sales_form', compact('company', 'selected_fields'));
    }
    
     public function ProductsItemsSalesAnalysisResult(Request $request, Company $company , $array = false )
    {
        $dimension = $request->report_type;

        $report_data =[];
        $growth_rate_data =[];
        $branches = is_array(json_decode(($request->branches[0]))) ? json_decode(($request->branches[0])) :$request->branches ;

        foreach ($branches as  $branch) {
			$branch  = replaceSingleQuote($branch);
			
		
                $branches_data =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,product_item
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND product_item = '".$branch."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id "
                )))->groupBy('gr_date')->map(function($item){
                    return $item->sum('net_sales_value');
                })->toArray();

            $interval_data_per_item = [];
            $years = [];
            if (count($branches_data)>0) {
              
                array_walk($branches_data, function ($val, $date) use (&$years) {
                    $years[] = date('Y', strtotime($date));
                });
                $years = array_unique($years);
                $report_data[$branch] = $branches_data;
                $interval_data_per_item[$branch] = $branches_data;
                $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$interval_data_per_item, $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);

                $report_data[$branch] = $interval_data['data_intervals'][$request->interval][$branch] ?? [];
                $growth_rate_data[$branch] = $this->growthRate($report_data[$branch]);
            }
        }

        $total_branches = $this->finalTotal($report_data);
        $total_branches_growth_rates =  $this->growthRate($total_branches);
        $final_report_data = [];
        $branches_names =[];
        foreach ($branches as  $branch) {
            $final_report_data[$branch]['Sales Values'] = ($report_data[$branch]??[]);
            $final_report_data[$branch]['Growth Rate %'] = ($growth_rate_data[$branch]??[]);
            $branches_names[] = (str_replace( ' ','_', $branch));
        }


        if($array)
        {
            return $report_data;
        }
		$dates = array_keys($total_branches ?? []); 
		$final_report_data = HArr::getKeysSortedDescByKey($final_report_data,'Sales Values');
        return view('client_view.reports.sales_gathering_analysis.products_items_sales_report',compact('company','branches_names','total_branches_growth_rates','final_report_data','total_branches','dates'));

    }

    public function  result(Request $request, Company $company , $secondReport = true)
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
        $growth_rate_data = [];
        $final_report_total = [];
        $Items_names = [];
        $mainData = is_array(json_decode(($request->ItemsData[0]))) ? json_decode(($request->ItemsData[0])) : $request->ItemsData;
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'net_sales_value' : 'quantity';
        // $request->report_type  
        if (isset($mainData[0]) && $mainData[0] == 'all') {
            $mainData =  SalesGathering::company()
                        ->where('product_item','!=','')
                        ->whereNotNull('product_item')
                        ->whereBetween('date', [$request->start_date,$request->end_date])
                        ->groupBy('product_item')
                        ->selectRaw('product_item')
                        ->get()
                        ->pluck('product_item')
                        ->toArray();
        }
        $type = $request->type;
        $view_name = $request->view_name;
        foreach ($mainData as  $main_row) {
                // $main_row = str_replace("'" , "''",$main_row);
				$main_row  = replaceSingleQuote($main_row);
            $mainData_data =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$data_type." ,product_item," . $type ."
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND product_item = '".$main_row."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id "
                )))->groupBy($type)->map(function($item)use($data_type){
                    return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){

                        return $sub_item->sum($data_type);
                    });
                })->toArray();

            foreach (($request->sales_channels ?? []) as $sales_channel_key => $sales_channel) {
                $years = [];
                $data_per_main_item = $mainData_data[$sales_channel]??[];
                if (count(($data_per_main_item))>0 ) {

                    // Data & Growth Rate Per Sales Channel
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                        $years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);

                    $report_data[$main_row][$sales_channel]['Sales Values'] = $data_per_main_item;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data[$main_row][$sales_channel], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
                    $report_data[$main_row][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];

                    $report_data[$main_row]['Total']  = $this->finalTotal([($report_data[$main_row]['Total']  ?? []) ,($report_data[$main_row][$sales_channel]['Sales Values']??[]) ]);
                    $report_data[$main_row][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data[$main_row][$sales_channel]['Sales Values'] ?? []));
                }
            }
            // Total & Growth Rate Per Zone

            $final_report_total = $this->finalTotal( [($report_data[$main_row]['Total']??[]) , ($final_report_total??[]) ]);
            $report_data[$main_row]['Growth Rate %'] =  $this->growthRate(($report_data[$main_row]['Total'] ?? []));

            $Items_names[] = (str_replace(' ', '_', $main_row));
        }
		


        $report_data['Total'] = $final_report_total;
        $report_data['Growth Rate %'] =  $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);
        // $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);
        $report_view = getComparingReportForAnalysis($request , $report_data , $secondReport , $company , $dates , $view_name , $Items_names , 'product_item' );
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
        return view('client_view.reports.sales_gathering_analysis.skus_analysis_report', compact('company','type', 'view_name', 'Items_names', 'dates', 'report_data',));
    }
    public function resultForSalesDiscount(Request $request, Company $company)
    {

        $report_data =[];
        $final_report_data =[];
        $growth_rate_data =[];
        $zones_names = [];
        $sales_values = [];
        $sales_years = [];
        $mainData = is_array(json_decode(($request->ItemsData[0]))) ? json_decode(($request->ItemsData[0])) :$request->ItemsData ;
        if (isset($mainData[0]) && $mainData[0] == 'all') {
            $mainData =  SalesGathering::company()
                        ->where('product_item','!=','')
                        ->whereNotNull('product_item')
                        ->groupBy('product_item')
                        ->selectRaw('product_item')
                        ->get()
                        ->pluck('product_item')
                        ->toArray();
        }
        $type = $request->type;
        $view_name = $request->view_name;
        $zones_discount = [];


        $fields ='';
        foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
            $fields .= $sales_discount_field .',';
        }


        foreach ($mainData as  $zone) {
			$zone  = replaceSingleQuote($zone);
            $sales =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields ." product_item
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND product_item = '".$zone."' AND date between '".$request->start_date."' and '".$request->end_date."')
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
        $total = ($final_report_data['Total']??[]);
        unset($final_report_data['Total']);
        $final_report_data['Total'] = $total;
        $final_report_data['Discount % / Total Sales'] = $this->operationAmongTwoArrays($final_report_data['Total'],$sales_values);

        // Total Zones & Growth Rate

        $report_data = $final_report_data;

        $dates = array_keys($report_data['Total']);
//  $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);
        $type_name = 'Products Items';
        return view('client_view.reports.sales_gathering_analysis.sales_discounts_analysis_report',compact('company','view_name','zones_names','dates','report_data','type_name'));

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
    // public function ProductsSalesAnalysisIndex(Company $company)
    // {
    //     // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
    //     $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
    //     return view('client_view.reports.sales_gathering_analysis.Items_sales_form', compact('company', 'selected_fields'));
    // }


    // public function ProductsSalesAnalysisResult(Request $request, Company $company)
    // {
    //     $dimension = $request->report_type;

    //     $report_data =[];
    //     $growth_rate_data =[];
    //     $Items = is_array(json_decode(($request->Items[0]))) ? json_decode(($request->Items[0])) :$request->Items ;

    //     foreach ($Items as  $category) {

    //         $sales_gatherings = SalesGathering::company()
    //                 ->where('category',$category)
    //                 ->whereBetween('date', [$request->start_date, $request->end_date])
    //                 ->selectRaw('DATE_FORMAT(date,"%d-%m-%Y") as date,net_sales_value,category')
    //                 ->get()
    //                 ->toArray();

    //         $Items_per_month = [];
    //         $Items_data = [];


    //         $dt = Carbon::parse($sales_gatherings[0]['date']);
    //         $month = $dt->endOfMonth()->format('d-m-Y');



    //         foreach ($sales_gatherings as $key => $row) {

    //             $dt = Carbon::parse($row['date']);
    //             $current_month = $dt->endOfMonth()->format('d-m-Y');
    //             if($current_month == $month){
    //                 $Items_per_month[$current_month][] = $row['net_sales_value'];

    //             }else{
    //                 $month = $current_month;
    //                 $Items_per_month[$current_month][] = $row['net_sales_value'];
    //             }

    //             $Items_data[$month] = array_sum($Items_per_month[$month]);
    //         }

    //         $report_data[$category] = $Items_data;
    //         $growth_rate_data[$category] = $this->growthRate($Items_data);

    //     }

    //     $total_Items = $this->finalTotal($report_data);
    //     $total_Items_growth_rates =  $this->growthRate($total_Items);
    //     $final_report_data = [];
    //     $Items_names =[];
    //     foreach ($Items as  $category) {
    //         $final_report_data[$category]['Sales Values'] = $report_data[$category];
    //         $final_report_data[$category]['Growth Rate %'] = $growth_rate_data[$category];
    //         $Items_names[] = (str_replace( ' ','_', $category));
    //     }

    //     return view('client_view.reports.sales_gathering_analysis.Items_sales_report',compact('company','Items_names','total_Items_growth_rates','final_report_data','total_Items'));

    // }


    public function Gaber1result(Request $request, Company $company)
    {

        $report_data = [];
        $growth_rate_data = [];
        $final_report_total = [];
        $Items_names = [];
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $date_one = new DateTime($start_date);
        $date_two = new DateTime($end_date);

        $diff = $date_two->diff($date_one);
        $number_of_years = $diff->y;
        $interval_dates = [];


        $intervals = [
            'monthly'  => ['01-01', '02-01', '03-01', '04-01', '05-01', '06-01', '07-01', '08-01', '09-01', '10-01', '11-01', '12-01'],
            'quarterly' => ['03-01', '06-01', '09-01', '12-01'],
            'semi-annually' => ['06-01', '12-01'],
            'annually'  => ['12-01']
        ];
        $mainData = is_array(json_decode(($request->ItemsData[0]))) ? json_decode(($request->ItemsData[0])) : $request->ItemsData;


        $type = $request->type;
        $view_name = $request->view_name;
        if (isset($mainData[0]) && $mainData[0] == 'all') {
            $mainData =  SalesGathering::company()
                        ->where('product_item','!=','')
                        ->whereNotNull('product_item')
                        ->groupBy('product_item')
                        ->selectRaw('product_item')
                        ->get()
                        ->pluck('product_item')
                        ->toArray();
        }

        $year = date('Y',strtotime($start_date));
        $previous_date =$date_one;
        for ($year_number=1; $year_number <= $number_of_years; $year_number++) {

            foreach ($intervals['quarterly'] as $month ) {
                $new_month = $year.'-'.$month;

                $interval_dates[] = $new_month;

            }

            $year++;
        }





        // gr_date Get last date of month
        foreach ($mainData as  $main_row) {
            $previous_date =$start_date;
			$main_row  = replaceSingleQuote($main_row);
            foreach ($interval_dates as $key => $date) {


                $report_data[$main_row]['Total'][date('d-m-Y',strtotime($date))] =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,product_item
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND product_item = '".$main_row."' AND date between '".$previous_date."' and '".$date."')
                ORDER BY id "
                )))->sum('net_sales_value');

                $previous_date =$date;
            }
        }







        // foreach ($mainData as  $main_row) {



        //     $mainData_data =collect(DB::select(DB::raw("
        //         SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,product_item," . $type ."
        //         FROM sales_gathering
        //         WHERE ( company_id = '".$company->id."'AND product_item = '".$main_row."' AND date between '".$request->start_date."' and '".$request->end_date."')
        //         ORDER BY id "
        //         )))->groupBy($type)->map(function($item){
        //             return $item->groupBy('gr_date')->map(function($sub_item){

        //                 return $sub_item->sum('net_sales_value');
        //             });
        //         })->toArray();


        //     foreach (($request->sales_channels ?? []) as $sales_channel_key => $sales_channel) {

        //         $years = [];

        //         $data_per_main_item = $mainData_data[$sales_channel]??[];
        //         if (count(($data_per_main_item))>0 ) {

        //             // Data & Growth Rate Per Sales Channel
        //             array_walk($data_per_main_item, function ($val, $date) use (&$years) {
        //                 $years[] = date('Y', strtotime($date));
        //             });
        //             $years = array_unique($years);

        //             $report_data[$main_row][$sales_channel]['Sales Values'] = $data_per_main_item;
        //             $interval_data = Intervals::intervals($report_data[$main_row][$sales_channel], $years, $request->interval);
        //             $report_data[$main_row][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];

        //             $report_data[$main_row]['Total']  = $this->finalTotal([($report_data[$main_row]['Total']  ?? []) ,($report_data[$main_row][$sales_channel]['Sales Values']??[]) ]);
        //             $report_data[$main_row][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data[$main_row][$sales_channel]['Sales Values'] ?? []));
        //         }
        //     }
        //     // Total & Growth Rate Per Zone

        //     $final_report_total = $this->finalTotal( [($report_data[$main_row]['Total']??[]) , ($final_report_total??[]) ]);
        //     $report_data[$main_row]['Growth Rate %'] =  $this->growthRate(($report_data[$main_row]['Total'] ?? []));

        //     $Items_names[] = (str_replace(' ', '_', $main_row));
        // }

        // Total Zones & Growth Rate


        // $report_data['Total'] = $final_report_total??[];
        $report_data['Total'] = [];
        // $report_data['Growth Rate %'] =  $this->growthRate($report_data['Total']);
        $report_data['Growth Rate %'] =  [];
        $dates = array_keys($report_data['Total']);


        return view('client_view.reports.sales_gathering_analysis.skus_analysis_report', compact('company', 'view_name', 'Items_names', 'dates', 'report_data',));
    }


    // Gaber Method 2
    public function G2result(Request $request, Company $company)
    {

        $report_data = [];
        $growth_rate_data = [];
        $final_report_total = [];
        $Items_names = [];
        $mainData = is_array(json_decode(($request->ItemsData[0]))) ? json_decode(($request->ItemsData[0])) : $request->ItemsData;

        if (isset($mainData[0]) && $mainData[0] == 'all') {
            $mainData =  SalesGathering::company()
                        ->whereNotNull('sku')
                        ->groupBy('sku')
                        ->selectRaw('sku')
                        ->get()
                        ->pluck('sku')
                        ->toArray();
        }
        $type = $request->type;
        $view_name = $request->view_name;

        foreach ($mainData as  $main_row) {

			$main_row  = replaceSingleQuote($main_row);


            $mainData_data =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,sku," . $type ."
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND sku = '".$main_row."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id "
                )))-> groupBy('gr_date')->map(function($sub_item){

                        return $sub_item->sum('net_sales_value');

                })->toArray();

                $report_data[$main_row]['Total']  = $mainData_data;
                // $this->finalTotal([($report_data[$main_row]['Total']  ?? []) ,($report_data[$main_row][$sales_channel]['Sales Values']??[]) ]);
                $report_data[$main_row]['Growth Rate %'] =[];

            // foreach (($request->sales_channels ?? []) as $sales_channel_key => $sales_channel) {

            //     $years = [];

            //     $data_per_main_item = $mainData_data[$sales_channel]??[];
            //     if (count(($data_per_main_item))>0 ) {

            //         // Data & Growth Rate Per Sales Channel
            //         array_walk($data_per_main_item, function ($val, $date) use (&$years) {
            //             $years[] = date('Y', strtotime($date));
            //         });
            //         $years = array_unique($years);

            //         $report_data[$main_row][$sales_channel]['Sales Values'] = $data_per_main_item;
            //         $interval_data = Intervals::intervals($report_data[$main_row][$sales_channel], $years, $request->interval);
            //         $report_data[$main_row][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];

            //         $report_data[$main_row]['Total']  = $this->finalTotal([($report_data[$main_row]['Total']  ?? []) ,($report_data[$main_row][$sales_channel]['Sales Values']??[]) ]);
            //         $report_data[$main_row][$sales_channel]['Growth Rate %'] =[];
            //         //  $this->growthRate(($report_data[$main_row][$sales_channel]['Sales Values'] ?? []));
            //     }
            // }
            // Total & Growth Rate Per Zone

            $final_report_total = $this->finalTotal( [($report_data[$main_row]['Total']??[]) , ($final_report_total??[]) ]);
            $report_data[$main_row]['Growth Rate %'] =[];
            // $this->growthRate(($report_data[$main_row]['Total'] ?? []));

            $Items_names[] = (str_replace(' ', '_', $main_row));
        }

        // Total Zones & Growth Rate


        $report_data['Total'] = $final_report_total;
        $report_data['Growth Rate %'] =[];
        // $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);


        return view('client_view.reports.sales_gathering_analysis.skus_analysis_report', compact('company', 'view_name', 'Items_names', 'dates', 'report_data',));
    }































    public function Newresult(Request $request, Company $company)
    {

        $report_data = [];
        $growth_rate_data = [];
        $final_report_total = [];
        $Items_names = [];
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $date_one = new DateTime($start_date);
        $date_two = new DateTime($end_date);

        $diff = $date_two->diff($date_one);
        $number_of_years = $diff->y;
        $interval_dates = [];


        $intervals = [
            'monthly'  => ['01-01', '02-01', '03-01', '04-01', '05-01', '06-01', '07-01', '08-01', '09-01', '10-01', '11-01', '12-01'],
            'quarterly' => ['03-01', '06-01', '09-01', '12-01'],
            'semi-annually' => ['06-01', '12-01'],
            'annually'  => ['12-01']
        ];
        $mainData = is_array(json_decode(($request->ItemsData[0]))) ? json_decode(($request->ItemsData[0])) : $request->ItemsData;


        $type = $request->type;
        $view_name = $request->view_name;
        if (isset($mainData[0]) && $mainData[0] == 'all') {
            $mainData =  SalesGathering::company()
                        ->where('product_item','!=','')
                        ->whereNotNull('product_item')
                        ->groupBy('product_item')
                        ->selectRaw('product_item')
                        ->get()
                        ->pluck('product_item')
                        ->toArray();
        }

        $year = date('Y',strtotime($start_date));
        $previous_date =$date_one;
        for ($year_number=1; $year_number <= $number_of_years; $year_number++) {

            foreach ($intervals['quarterly'] as $month ) {
                $new_month = $year.'-'.$month;

                $interval_dates[] = $new_month;

            }

            $year++;
        }












        foreach ($mainData as  $main_row) {
			$main_row  = replaceSingleQuote($main_row);
			
            $previous_date =$start_date;
            foreach ($interval_dates as $key => $date) {


                $report_data[$main_row]['Total'][date('d-m-Y',strtotime($date))] =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,product_item
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND product_item = '".$main_row."' AND date between '".$previous_date."' and '".$date."')
                ORDER BY id "
                )))->sum('net_sales_value');

                $previous_date =$date;
            }
        }






        $report_data['Total'] = $final_report_total??[];
        $report_data['Growth Rate %'] =  $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);


        return view('client_view.reports.sales_gathering_analysis.Items_analysis_report', compact('company', 'view_name', 'Items_names', 'dates', 'report_data',));
    }
}
