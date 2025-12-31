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

class SalesPersonsAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {
        if (request()->route()->named('salesPersons.sales.analysis')) {
            $type = 'sales_person';
            $view_name = 'Sales Persons Trend Analysis';
        } elseif (request()->route()->named('salesPersons.zones.analysis')) {
            $type = 'zone';
            $view_name = 'Sales Persons Against Zones Trend Analysis';
        } elseif (request()->route()->named('salesPersons.customers.analysis')) {
            $type = 'customer_name';
            $view_name = 'Sales Persons Against Customers Trend Analysis';
        }
		elseif (request()->route()->named('salesPersons.countries.analysis')) {
            $type = 'country';
            $view_name = 'Sales Persons Against Countries Trend Analysis';
        }
		elseif (request()->route()->named('salesPersons.salesChannels.analysis')) {
            $type  = 'sales_channel';
            $view_name = 'Sales Persons Against Sales Channels Trend Analysis';
        } elseif (request()->route()->named('salesPersons.categories.analysis')) {
            $type  = 'category';
            $view_name = 'Sales Persons Against Categories Trend Analysis';
        } elseif (request()->route()->named('salesPersons.principles.analysis')) {
            $type  = 'principle';
            $view_name = 'Sales Persons Against Principles Trend Analysis';
        } elseif (request()->route()->named('salesPersons.products.analysis')) {
            $type  = 'product_or_service';
            $view_name = 'Sales Persons Against Products / Services Trend Analysis';
        } elseif (request()->route()->named('salesPersons.Items.analysis')) {
            $type  = 'product_item';
            $view_name = 'Sales Persons Against Products Items Trend Analysis';
        } elseif (request()->route()->named('salesPersons.salesDiscount.analysis')) {
            $type  = 'quantity_discount';
            $view_name = 'Sales Persons Against Sales Discount Trend Analysis';
        } elseif (request()->route()->named('salesPersons.businessSectors.analysis')) {
            $type  = 'business_sector';
            $view_name = 'Sales Persons Against Business Sectors Trend Analysis';
        }elseif (request()->route()->named('salesPersons.businessUnits.analysis')) {
            $type  = 'business_unit';
            $view_name = 'Sales Persons Against Business Units Trend Analysis';
        } elseif (request()->route()->named('salesPersons.branches.analysis')) {
            $type  = 'branch';
            $view_name = 'Sales Persons Against Branches Trend Analysis';
        }
        $name_of_selector_label = str_replace(['Sales Persons Against ', ' Trend Analysis'], '', $view_name);
        return view('client_view.reports.sales_gathering_analysis.salesPersons_analysis_form', compact('company', 'name_of_selector_label', 'type', 'view_name'));
    }
    public function result(Request $request, Company $company , $secondReport = true)
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
        $salesPersons_names = [];

        $mainData = is_array(json_decode(($request->salesPersonsData[0]))) ? json_decode(($request->salesPersonsData[0])) : $request->salesPersonsData;
        $type = $request->type;
        $view_name = $request->view_name;
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'net_sales_value' : 'quantity';
        foreach ($mainData as  $main_row) {

            $mainData_data =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$data_type." ,sales_person," . $type ."
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND sales_person = '".$main_row."' AND date between '".$request->start_date."' and '".$request->end_date."')
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

            $salesPersons_names[] = (str_replace(' ', '_', $main_row));
        }

        // Total Zones & Growth Rate


        $report_data['Total'] = $final_report_total;
        $report_data['Growth Rate %'] =  $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);
        
        // $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);

         $Items_names = $salesPersons_names ;
         $report_view = getComparingReportForAnalysis($request , $report_data , $secondReport , $company , $dates , $view_name , $Items_names , 'sales_person' );

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



        return view('client_view.reports.sales_gathering_analysis.salesPersons_analysis_report', compact('company', 'view_name', 'salesPersons_names', 'dates', 'report_data',));
    }


    public function resultForSalesDiscount(Request $request, Company $company)
    {

        $report_data =[];
        $final_report_data =[];
        $growth_rate_data =[];
        $zones_names = [];
        $sales_values = [];
        $sales_years = [];
        $zones = is_array(json_decode(($request->salesPersonsData[0]))) ? json_decode(($request->salesPersonsData[0])) :$request->salesPersonsData ;
        $type = $request->type;
        $view_name = $request->view_name;
        $zones_discount = [];


        $fields ='';
        foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
            $fields .= $sales_discount_field .',';
        }


        foreach ($zones as  $zone) {

            $sales =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields ." sales_person
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND sales_person = '".$zone."' AND date between '".$request->start_date."' and '".$request->end_date."')
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
       

 
        $type_name = 'Sales Persons';
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
    //     return view('client_view.reports.sales_gathering_analysis.salesPersons_sales_form', compact('company', 'selected_fields'));
    // }


    // public function ProductsSalesAnalysisResult(Request $request, Company $company , $array = false)
    // {
    //     // uncommented by salah
    //     $dimension = $request->report_type;

    //     $report_data =[];
    //     $growth_rate_data =[];
    //     $salesPersons = is_array(json_decode(($request->salesPersons[0]))) ? json_decode(($request->salesPersons[0])) :$request->salesPersons ;

    //     foreach ($salesPersons as  $category) {

    //         $sales_gatherings = SalesGathering::company()
    //                 ->where('sales_person',$category)
    //                 ->whereBetween('date', [$request->start_date, $request->end_date])
    //                 ->selectRaw('DATE_FORMAT(date,"%d-%m-%Y") as date,net_sales_value,sales_person')
    //                 ->get()
    //                 ->toArray();

    //         $salesPersons_per_month = [];
    //         $salesPersons_data = [];





    //         foreach ($sales_gatherings as $key => $row) {

    //             $dt = Carbon::parse($row['date']);
    //             $current_month = $dt->endOfMonth()->format('d-m-Y');
    //             if($current_month == $month){
    //                 $salesPersons_per_month[$current_month][] = $row['net_sales_value'];

    //             }else{
    //                 $month = $current_month;
    //                 $salesPersons_per_month[$current_month][] = $row['net_sales_value'];
    //             }

    //             $salesPersons_data[$month] = array_sum($salesPersons_per_month[$month]);
    //         }

    //         $report_data[$category] = $salesPersons_data;
    //         $growth_rate_data[$category] = $this->growthRate($salesPersons_data);

    //     }

    //     $total_salesPersons = $this->finalTotal($report_data);
    //     $total_salesPersons_growth_rates =  $this->growthRate($total_salesPersons);
    //     $final_report_data = [];
    //     $salesPersons_names =[];
    //     foreach ($salesPersons as  $category) {
    //         $final_report_data[$category]['Sales Values'] = $report_data[$category];
    //         $final_report_data[$category]['Growth Rate %'] = $growth_rate_data[$category];
    //         $salesPersons_names[] = (str_replace( ' ','_', $category));
    //     }
    //     if($array)
    //     {
    //         return $report_data;
    //     }
	// $final_report_data = HArr::getKeysSortedDescByKey($final_report_data,'Sales Values');
    //     return view('client_view.reports.sales_gathering_analysis.salesPersons_sales_report',compact('company','salesPersons_names','total_salesPersons_growth_rates','final_report_data','total_salesPersons'));

    // }



}
