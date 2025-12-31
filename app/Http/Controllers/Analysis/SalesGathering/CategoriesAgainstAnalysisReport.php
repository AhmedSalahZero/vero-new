<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {

        if (request()->route()->named('categories.zones.analysis')) {
            $type = 'zone';
            $view_name = 'Categories Against Zones Trend Analysis' ;
        } elseif (request()->route()->named('categories.customers.analysis')) {
            $type = 'customer_name';
            $view_name = 'Categories Against Customers Trend Analysis' ;
        } elseif (request()->route()->named('categories.salesChannels.analysis')) {
            $type  = 'sales_channel';
            $view_name = 'Categories Against Sales Channels Trend Analysis' ;
        } elseif (request()->route()->named('categories.products.analysis')) {
            $type  = 'product_or_service';
            $view_name = 'Categories Against Products / Services Trend Analysis' ;
        } elseif (request()->route()->named('categories.countries.analysis')) {
            $type  = 'country';
            $view_name = 'Categories Against Countries Trend Analysis' ;
        } elseif (request()->route()->named('categories.Items.analysis')) {
            $type  = 'product_item';
            $view_name = 'Categories Against Products Items Trend Analysis' ;
        } elseif (request()->route()->named('categories.salesPersons.analysis')) {
            $type  = 'sales_person';
            $view_name = 'Categories Against Sales Persons Trend Analysis' ;
        } elseif (request()->route()->named('categories.salesDiscount.analysis')) {
            $type  = 'quantity_discount';
            $view_name = 'Categories Against Sales Discount Trend Analysis' ;
        } elseif (request()->route()->named('categories.businessSectors.analysis')) {
            $type  = 'business_sector';
            $view_name = 'Categories Against Business Sectors Trend Analysis' ;
        } elseif (request()->route()->named('categories.businessUnits.analysis')) {
            $type  = 'business_unit';
            $view_name = 'Categories Against Business Units Trend Analysis' ;
        } elseif (request()->route()->named('categories.branches.analysis')) {
            $type  = 'branch';
            $view_name = 'Categories Against Branches Trend Analysis' ;
        } elseif (request()->route()->named('categories.products.averagePrices')) {
            $type  = 'averagePrices';
            $view_name = 'Categories Products / Services Average Prices' ;
        } elseif (request()->route()->named('customers.categories.analysis')) {
            $type  = 'category';
            $view_name = Customers_Against_Categories_Trend_Analysis ;
        } elseif (request()->route()->named('customers.products.analysis')) {
            // by salah
            $type  = 'product_or_service';
            $view_name = Customers_Against_Products_Trend_Analysis ;
        } elseif (request()->route()->named('customers.Items.analysis')) {
            $type  = 'product_item';
            $view_name = Customers_Against_Products_ITEMS_Trend_Analysis ;
        } elseif (request()->route()->named('categories.principles.analysis')) {
            $type  = 'principle';
            $view_name = 'Categories Against Principle' ;
        } elseif (request()->route()->named('customers.principles.analysis')) {
            $type  = 'principle';
            $view_name = 'Customers Against Principle' ;
        } elseif (request()->route()->named('categories.day.analysis')) {
            $type  = 'day_name';
            $view_name = 'Categories Against Days' ;
        }
        $name_of_selector_label = str_replace(['Categories Against ' ,' Trend Analysis'], '', $view_name);
        return view('client_view.reports.sales_gathering_analysis.categories_analysis_form', compact('company', 'name_of_selector_label', 'type', 'view_name'));
    }
    public function CategoriesSalesAnalysisIndex(Company $company)
    {
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
        return view('client_view.reports.sales_gathering_analysis.categories_sales_form', compact('company', 'selected_fields'));
    }
    public function result(Request $request, Company $company, $result='view', $secondReport = true)
    {
        
        if ($request->report_type =='comparing' && $secondReport == true) {
            $firstReportStartDate = $request->get('start_date_second');
            $firstReportEndDate = $request->get('end_date_second');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            if (Carbon::make($firstReportEndDate)->lessThan(Carbon::make($endDate))) {
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
        $categories_names = [];
        $zones = is_array(json_decode(($request->categoriesData[0]))) ? json_decode(($request->categoriesData[0])) :$request->categoriesData ;
        $type = $request->type;
        $view_name = $request->view_name;
        $name_of_report_item  = ($result=='view') ? 'Sales Values' : 'Avg. Prices';
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'net_sales_value' : 'quantity';
        foreach ($zones as $zone) {

            if ($result == 'view') {
                $mainField = 'category';
                if (($type == 'category' && $view_name ==Customers_Against_Categories_Trend_Analysis)
                || ($type == 'product_or_service' && $view_name == Customers_Against_Products_Trend_Analysis)
                || ($type == 'product_item' && $view_name == Customers_Against_Products_ITEMS_Trend_Analysis)
                
                ) {
                    $mainField = 'customer_name';
                }
                
                $zones_data =collect(DB::select(DB::raw(
                    "
                    SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$data_type." ,category," . $type ."
                    FROM sales_gathering
                    WHERE ( company_id = '".$company->id."'AND ". $mainField ."  = '".$zone."' AND date between '".$request->start_date."' and '".$request->end_date."')
                    ORDER BY id "
                )))->groupBy($type)->map(function ($item) use ($data_type) {
                    return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {
                        
                        return $sub_item->sum($data_type);
                    });
                })->toArray();
			
            } else {
                $zones_data = DB::table('sales_gathering')
                    ->where('company_id', $company->id)
                    ->where('category', $zone)
                    ->whereNotNull($type)
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,
                    (IFNULL('.$data_type.',0) ) as '.$data_type.' ,category,' . $type)
                    ->get()
                    ->groupBy($type)->map(function ($item) use ($data_type) {
                        return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {
                            return $sub_item->sum($data_type);
                        });
                    })->toArray();

                $zones_data_quantity = DB::table('sales_gathering')
                    ->where('company_id', $company->id)
                    ->where('category', $zone)
                    ->whereNotNull($type)
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date ,
                    (IFNULL('.$data_type.',0)  ) as '.$data_type.' ,IFNULL(quantity_bonus,0) quantity_bonus , IFNULL(quantity,0) quantity,category,' . $type)
                    ->get()
                    ->groupBy($type)->map(function ($item) use ($data_type) {
                        return $item->groupBy('gr_date')->map(function ($sub_item) use ($data_type) {
                            return ($sub_item->sum('quantity_bonus') + $sub_item->sum('quantity')) ;
                        });
                    })->toArray();

            }

            foreach (($request->sales_channels??[]) as $sales_channel_key => $sales_channel) {

                $years = [];
                $data_per_main_item = $zones_data[$sales_channel]??[];
                if (count(($data_per_main_item))>0) {
                    // Data & Growth Rate Per Sales Channel
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                        $years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);
                    $report_data[$zone][$sales_channel][$name_of_report_item] = $data_per_main_item;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'), $report_data[$zone][$sales_channel], $years, $request->interval, 'multi', 'intervals_summation', true, true, true, explode('-', $request->get('start_date'))[1]);

					
                    $report_data[$zone][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];
		
                    $report_data[$zone]['Total']  = $this->finalTotal([($report_data[$zone]['Total']  ?? []) ,($report_data[$zone][$sales_channel][$name_of_report_item]??[]) ]);
                    $report_data[$zone][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data[$zone][$sales_channel][$name_of_report_item] ?? []));

                }
            }
            if ($result == 'array') {
                
                foreach (($request->sales_channels??[]) as $sales_channel_key => $sales_channel) {

                    $years = [];

                    $data_per_main_item = $zones_data_quantity[$sales_channel]??[];
                    if (count(($data_per_main_item))>0) {
                        // Data & Growth Rate Per Sales Channel
                        array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                            $years[] = date('Y', strtotime($date));
                        });
                        $years = array_unique($years);

                        $report_data_quantity[$zone][$sales_channel][$name_of_report_item] = $data_per_main_item;
                        $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'), $report_data_quantity[$zone][$sales_channel], $years, $request->interval, 'multi', 'intervals_summation', true, true, true, explode('-', $request->get('start_date'))[1]);
                        $report_data_quantity[$zone][$sales_channel] = $interval_data['data_intervals'][$request->interval] ?? [];

                        $report_data_quantity[$zone]['Total']  = $this->finalTotal([($report_data_quantity[$zone]['Total']  ?? []) ,($report_data_quantity[$zone][$sales_channel][$name_of_report_item]??[]) ]);
                        $report_data_quantity[$zone][$sales_channel]['Growth Rate %'] = $this->growthRate(($report_data_quantity[$zone][$sales_channel][$name_of_report_item] ?? []));

                    }
                }


                foreach ($report_data as $reportType=>$dates) {
                    // Baby 20
                
                    if ($zone  == $reportType) {
                        foreach ($dates as $dateName=>$items) {
                            if ($dateName != 'Total') {
                                //Avg. Prices
                                foreach ($items as $itemKey=> $values) {
                                    if ($itemKey == 'Avg. Prices') {
                                        foreach ($values as $datee => $dateVal) {
                                            $report_data[$reportType][$dateName][$itemKey][$datee] =
                                            $report_data_quantity[$reportType][$dateName][$itemKey][$datee] ?
                                            $report_data[$reportType][$dateName][$itemKey][$datee] / $report_data_quantity[$reportType][$dateName][$itemKey][$datee]
                                            : 0 ;

                                            $report_data[$reportType]['Totals'][$datee] = $report_data[$reportType][$dateName][$itemKey][$datee] + ($report_data[$reportType]['Totals'][$datee] ??0);
                          
                            
                                            $report_data[$reportType]['Total'][$datee] = $report_data[$reportType]['Totals'][$datee];
                            
                                        }
                        
                        
                                    } elseif ($itemKey == 'Growth Rate %') {
                                        foreach ($values as $datee => $dateVal) {
                                            $report_data[$reportType][$dateName]['Avg. Prices'][$datee];
                                            $keys = array_flip(array_keys($report_data[$reportType][$dateName]['Avg. Prices']));
                                            $values = array_values($report_data[$reportType][$dateName]['Avg. Prices']);
                                            $previousValue = isset($values[$keys[$datee]-1]) ? $values[$keys[$datee]-1] : 0 ;
                                            $report_data[$reportType][$dateName][$itemKey][$datee] =  $previousValue ? (($report_data[$reportType][$dateName]['Avg. Prices'][$datee] - $previousValue)/ $previousValue)*100 : 0;
                          
                                        }
                                    }
                        
                        
                        
                        
                                }
                            }
                
                        }
                    }
                             

                             
                
                }
            
            
                
            }
            // Total & Growth Rate Per Zone
            $final_report_total = $this->finalTotal([($report_data[$zone]['Total']??[]) , ($final_report_total??[]) ]);
            $report_data[$zone]['Growth Rate %'] =  $this->growthRate(($report_data[$zone]['Total']??[]));
            $categories_names[] = (str_replace(' ', '_', $zone));

        }
        foreach ($report_data as $r=>$d) {
            unset($report_data[$r]['Totals']);
        }

        
        // Total Zones & Growth Rate


        $report_data['Total'] = $final_report_total;
        $report_data['Growth Rate %']=  $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);
        //  $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);
        $Items_names = $categories_names ;
        $report_view = getComparingReportForAnalysis($request, $report_data, $secondReport, $company, $dates, $view_name, $Items_names, 'category');
        if ($report_view instanceof View) {
            return $report_view ;
        }
        if ($request->report_type =='comparing') {
            return [
                'report_data'=>$report_data ,
                'dates'=>$dates ,
                'full_date' =>Carbon::make($request->start_date)->format('d M Y') .' '.__('To').' '.Carbon::make($request->end_date)->format('d M Y')
            ];
        }
        if ($result=='view') {
            
            return view('client_view.reports.sales_gathering_analysis.categories_analysis_report', compact('company', 'type', 'name_of_report_item', 'view_name', 'categories_names', 'dates', 'report_data'));
        } else {
            return [ 'report_data'=>$report_data,'view_name'=>$view_name,'names'=> $categories_names];
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
        $zones = is_array(json_decode(($request->categoriesData[0]))) ? json_decode(($request->categoriesData[0])) :$request->categoriesData ;

        $type = $request->type;
        $view_name = $request->view_name;

        $fields ='';
        foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
            $fields .= $sales_discount_field .',';
        }


        foreach ($zones as $zone) {

            $sales =collect(DB::select(DB::raw(
                "
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields ." category
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND category = '".$zone."' AND date between '".$request->start_date."' and '".$request->end_date."')
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

                    // Data & Growth Rate Per Sales Channel


                    array_walk($zones_discount, function ($val, $date) use (&$discount_years) {
                        $discount_years[] = date('Y', strtotime($date));
                    });
                    $discount_years = array_unique($discount_years);

                    array_walk($zones_sales_values, function ($val, $date) use (&$sales_years) {
                        $sales_years[] = date('Y', strtotime($date));
                    });
                    $sales_years = array_unique($sales_years);



                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'), $sales_values_per_zone, $sales_years, $request->interval, 'multi', 'intervals_summation', true, true, true, explode('-', $request->get('start_date'))[1]);

                    $sales_values[$zone]  = $interval_data['data_intervals'][$request->interval][$zone] ?? [];




                    $final_report_data[$zone][$sales_discount_field]['Values'] = $zones_discount;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'), $final_report_data[$zone][$sales_discount_field], $discount_years, $request->interval, 'multi', 'intervals_summation', true, true, true, explode('-', $request->get('start_date'))[1]);
                    $final_report_data[$zone][$sales_discount_field] = $interval_data['data_intervals'][$request->interval] ?? [];


                    $final_report_data[$zone]['Total']  = $this->finalTotal([($final_report_data[$zone]['Total']  ?? []) ,($final_report_data[$zone][$sales_discount_field]['Values']??[]) ]);






                    $final_report_data['Total'] = $this->finalTotal([($final_report_data['Total'] ?? []), (($final_report_data[$zone][$sales_discount_field]['Values']??[]))]);


                    $final_report_data[$zone][$sales_discount_field]['Perc.% / Sales'] = $this->operationAmongTwoArrays(($final_report_data[$zone][$sales_discount_field]['Values']??[]), ($sales_values[$zone]??[]));


                }
            }
            $zones_names[] = (str_replace(' ', '_', $zone));
        }

        $sales_values = $this->finalTotal([$sales_values??[]]);
        $total = $final_report_data['Total'] ?? [];
        unset($final_report_data['Total']);
        $final_report_data['Total'] = $total;
        $final_report_data['Discount % / Total Sales'] = $this->operationAmongTwoArrays($final_report_data['Total'], $sales_values);

        // Total Zones & Growth Rate

        $report_data = $final_report_data;

        $dates = array_keys(($report_data['Total']??[]));

        $type_name = 'Categories';
        return view('client_view.reports.sales_gathering_analysis.sales_discounts_analysis_report', compact('company', 'view_name', 'zones_names', 'dates', 'report_data', 'type_name'));

    }


    public function CategoriesSalesAnalysisResult(Request $request, Company $company)
    {
        $dimension = $request->report_type;

        $report_data =[];
        $growth_rate_data =[];
        $categories = is_array(json_decode(($request->categories[0]))) ? json_decode(($request->categories[0])) :$request->categories ;

        foreach ($categories as $category) {

            $categories_data = [];



            $categories_data =collect(DB::select(DB::raw(
                "
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,category
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND category = '".$category."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id "
            )))->groupBy('gr_date')->map(function ($item) {
                return $item->sum('net_sales_value');
            })->toArray();


            $interval_data_per_item = [];
            $years = [];
            if (count($categories_data)>0) {

                // Data & Growth Rate Per Sales Channel
                array_walk($categories_data, function ($val, $date) use (&$years) {
                    $years[] = date('Y', strtotime($date));
                });
                $years = array_unique($years);
                $report_data[$category] = $categories_data;
                $interval_data_per_item[$category] = $categories_data;
                $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'), $interval_data_per_item, $years, $request->interval, 'multi', 'intervals_summation', true, true, true, explode('-', $request->get('start_date'))[1]);

                $report_data[$category] = $interval_data['data_intervals'][$request->interval][$category] ?? [];
                $growth_rate_data[$category] = $this->growthRate($report_data[$category]);



            }
        }

        $total_categories = $this->finalTotal($report_data);
        $total_categories_growth_rates =  $this->growthRate($total_categories);
        $final_report_data = [];
        $categories_names =[];
        foreach ($categories as $category) {
            $final_report_data[$category]['Sales Values'] = ($report_data[$category]??[]);
            $final_report_data[$category]['Growth Rate %'] = ($growth_rate_data[$category]??[]);
            $categories_names[] = (str_replace(' ', '_', $category));
        }
        $dates = array_keys($total_categories ?? []);
        $final_report_data = HArr::getKeysSortedDescByKey($final_report_data, 'Sales Values');
        
        return view('client_view.reports.sales_gathering_analysis.categories_sales_report', compact('company', 'categories_names', 'total_categories_growth_rates', 'final_report_data', 'total_categories', 'dates'));

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
            } else {

                $final_data[$date] = $prev_month != 0 ? (($value - $prev_month) / $prev_month) * 100 : 0;
            }
            $prev_month = $value;
        }
        return $final_data;
    }


}
