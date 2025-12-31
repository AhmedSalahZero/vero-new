<?php
namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProvidersTwodimensionalSalesBreakdownAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Request $request, Company $company)
    {
        //service_provider_name
        if (request()->route()->named('serviceProviders.vs.businessSectors.view')) {
            $main_type = 'service_provider_name';
            $type = 'business_sector';
            $view_name = 'Service Provider Versus Business Sectors' ;
        }elseif (request()->route()->named('serviceProviders.vs.branches.view')) {
            $main_type = 'service_provider_name';
            $type = 'branch';
            $view_name = 'Service Provider Versus Branches' ;
        }elseif (request()->route()->named('serviceProviders.vs.salesChannels.view')) {
            $main_type = 'service_provider_name';
            $type = 'sales_channel';
            $view_name = 'Service Provider Versus Sales Channels' ;
        }elseif (request()->route()->named('serviceProviders.vs.products.view')) {
            $main_type = 'service_provider_name';
            $type = 'product_or_service';
            $view_name = 'Service Provider Versus Products / Services' ;
        }

        //serviceProvidersType
        elseif(request()->route()->named('serviceProvidersType.vs.businessSectors.view')) {
            $type = 'service_provider_type';
            $main_type = 'business_sector';
            $view_name = 'Service Provider Type Versus Business Sectors' ;
        }elseif(request()->route()->named('serviceProvidersType.vs.businessUnits.view')) {
            $type = 'service_provider_type';
            $main_type = 'business_unit';
            $view_name = 'Service Provider Type Versus Business Units' ;
        }elseif (request()->route()->named('serviceProvidersType.vs.branches.view')) {
            $type = 'service_provider_type';
            $main_type = 'branch';
            $view_name = 'Service Provider Type Versus Branches' ;
        }elseif (request()->route()->named('serviceProvidersType.vs.salesChannels.view')) {
            $type = 'service_provider_type';
            $main_type = 'sales_channel';
            $view_name = 'Service Provider Type Versus Sales Channels' ;
        }elseif (request()->route()->named('serviceProvidersType.vs.products.view')) {
            $type = 'service_provider_type';
            $main_type = 'product_or_service';
            $view_name = 'Service Provider Type Versus Products / Services' ;
        }

        //serviceProvidersBirthYear
        elseif(request()->route()->named('serviceProvidersBirthYear.vs.businessSectors.view')) {
            $type = 'service_provider_birth_year';
            $main_type = 'business_sector';
            $view_name = 'Service Provider Age Range Versus Business Sectors' ;
        }elseif (request()->route()->named('serviceProvidersBirthYear.vs.branches.view')) {
            $type = 'service_provider_birth_year';
            $main_type = 'branch';
            $view_name = 'Service Provider Age Range Versus Branches' ;
        }elseif (request()->route()->named('serviceProvidersBirthYear.vs.salesChannels.view')) {
            $type = 'service_provider_birth_year';
            $main_type = 'sales_channel';
            $view_name = 'Service Provider Age Range Versus Sales Channels' ;
        }elseif (request()->route()->named('serviceProvidersBirthYear.vs.products.view')) {
            $main_type = 'product_or_service';
            $type = 'service_provider_birth_year';
            $view_name = 'Service Provider Age Range Versus Products / Services' ;
        }


        return view('client_view.reports.sales_gathering_analysis.providers_two_dimensional_breakdown.sales_form', compact('company', 'view_name','type','main_type'));
    }
    public function result(Request $request, Company $company)
    {

        $report_data =[];
        $main_type = $request->main_type;
        $type = $request->type;
        $view_name = $request->view_name;
        $last_date = null;
        $dates = [
            'start_date' => date('d-M-Y',strtotime($request->start_date)),
            'end_date' => date('d-M-Y',strtotime($request->end_date))
        ];
        $all_items = [];

        $main_type_items_totals = [];



        $report_data =collect(DB::select(DB::raw("
            SELECT DATE_FORMAT(date,'%d-%m-%Y') as date, net_sales_value ,sales_value,service_provider_name,".$type.",".$main_type ."
            FROM sales_gathering
            WHERE ( company_id = '".$company->id."' AND ".$type." IS NOT NULL AND ".$main_type." IS NOT NULL  AND date between '".$request->start_date."' and '".$request->end_date."')
            ORDER BY id "
            )))->groupBy($main_type)->map(function($item) use($type){
                return $item->groupBy($type)->map(function($sub_item){
                    return $sub_item->sum('net_sales_value');
                });
            })->toArray();



            if ($type == 'service_provider_birth_year') {
                $report_view_data = [];

                $current_date = date('Y');



                foreach ($report_data as $type_name => $data_per_type) {
                    foreach ($data_per_type as $year => $data_per_year) {

                        $age = $current_date - $year ;
                        if ($age <= 40) {
                            $age_range ='Age Range Less Than 40';
                        }elseif ($age >= 41 && $age <= 50) {
                            $age_range ='Age Range 41 - 50';
                        }elseif ($age >= 51 && $age <= 60) {
                            $age_range ='Age Range 51 - 60';
                        }elseif ($age >  60) {
                            $age_range ='Age Range Over 60';
                        }
                        $report_view_data[$type_name][$age_range] = ($report_view_data[$type_name][$age_range]??0) + $data_per_year;
                    }
                }


                // $report_view_data = [
                //     'Age Range 30 - 40'=>$this->finalTotal($report_view_data['Age Range 30 - 40']),
                //     'Age Range 41 - 50'=>$this->finalTotal($report_view_data['Age Range 41 - 50']),
                //     'Age Range 51 - 60'=>$this->finalTotal($report_view_data['Age Range 51 - 60']),
                //     'Age Range Over 60'=>$this->finalTotal($report_view_data['Age Range Over 60']),
                // ];
                $report_data = [];
                $report_data = $report_view_data;
            }


        $main_type_items = array_keys(($report_data??[]));
        foreach ($report_data as  $main_type_item_name => $sales_gathering_data) {
            $main_type_items_totals[$main_type_item_name] = array_sum($report_data[$main_type_item_name]??[]);
        }

        $items_totals = $this->finalTotal([$report_data]);
        $all_items =   array_keys($items_totals);
        arsort($main_type_items_totals);


        if(count($main_type_items_totals) > 50){
            $report_view_data = collect($main_type_items_totals);
            $top_20 = $report_view_data->take(50);
            $report_view_data = $top_20->toArray();
            $main_type_items_totals = $report_view_data;
            foreach ($report_view_data as $name_of_main_item => $data) {
                $result[$name_of_main_item] =$report_data[$name_of_main_item];
                unset($report_data[$name_of_main_item]);
            }
            $result['Others '.count($report_data)] =  $this->finalTotal([$report_data]);

            $main_type_items_totals['Others '.count($report_data)]  = array_sum(($result['Others '.count($report_data)]??[]));
            $report_data = $result;

        }

        $last_date = SalesGathering::company()->latest('date')->first()->date;
        $last_date = date('d-M-Y',strtotime($last_date));
        if ($type == 'service_provider_birth_year') {
            $all_items = [
                            'Age Range Less Than 40',
                            'Age Range 41 - 50',
                            'Age Range 51 - 60',
                            'Age Range Over 60',
                        ];
        }else {
            $all_items = array_unique($all_items);
        }

        return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_report',compact('company','view_name', 'main_type','type', 'all_items','main_type_items','report_data','last_date','dates','items_totals','main_type_items_totals'));

    }


    public function discountsResult(Request $request, Company $company)
    {
        {

            $report_data =[];
            $main_type = $request->main_type;
            $type = 'discounts';
            $view_name = $request->view_name;
            $last_date = null;
            $dates = [
                'start_date' => date('d-M-Y',strtotime($request->start_date)),
                'end_date' => date('d-M-Y',strtotime($request->end_date))
            ];
            $all_items = [];
            $main_type_items = SalesGathering::company()->whereNotNull($main_type)->groupBy($main_type)->selectRaw($main_type)->whereBetween('date', [$request->start_date, $request->end_date])->get()->pluck($main_type)->toArray();
            // $items = SalesGathering::company()->whereNotNull($main_type)->groupBy($main_type)->selectRaw($main_type)->whereBetween('date', [$request->start_date, $request->end_date])->get()->pluck($main_type)->toArray();
            $all_items = [
                'quantity_discount' => 'Quantity Discount' ,
                'cash_discount' => 'Cash Discount' ,
                'special_discount' => 'Special Discount' ,
                'other_discounts' => 'Other Discount' ,
            ];
            $totals_sales_per_main_type = [];
            $total_sales = 0;
            foreach ($main_type_items as  $main_type_item_name) {


                    foreach ($all_items as  $field => $field_name) {
                        $sales_gatherings = SalesGathering::company()
                                    ->where($main_type,$main_type_item_name)
                                    ->whereBetween('date', [$request->start_date, $request->end_date])
                                    ->selectRaw('DATE_FORMAT(date,"%d-%m-%Y") as date,sales_value,'.$field.','.$main_type)
                                    ->get();

                        $field_total = collect($sales_gatherings)->sum('sales_value');
                        $total_sales += $field_total ;
                        $totals_sales_per_main_type[$main_type_item_name] =  $field_total;
                        $main_type_items_per_month = [];
                        $main_type_items_data = [];

                        $total = collect($sales_gatherings)->sum($field);

                        $report_data[$main_type_item_name][$field_name] =$total;


                    }


                $main_type_items_totals[$main_type_item_name] = array_sum($report_data[$main_type_item_name]??[]);
            }

            $items_totals = $this->finalTotal([$report_data]);
            arsort($main_type_items_totals);


            if(count($main_type_items_totals) > 50){
                $report_view_data = collect($main_type_items_totals);
                $top_20 = $report_view_data->take(50);
                $report_view_data = $top_20->toArray();
                $main_type_items_totals = $report_view_data;
                foreach ($report_view_data as $name_of_main_item => $data) {
                    $result[$name_of_main_item] =$report_data[$name_of_main_item];
                    unset($report_data[$name_of_main_item]);
                }
                $result['Others '.count($report_data)] =  $this->finalTotal([$report_data]);

                $main_type_items_totals['Others '.count($report_data)]  = array_sum(($result['Others '.count($report_data)]??[]));
                $report_data = $result;
            }

            $last_date = SalesGathering::company()->latest('date')->first()->date;
            $last_date = date('d-M-Y',strtotime($last_date));
            $all_items = array_unique($all_items);

            return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_report',compact('company','view_name', 'main_type','type','all_items','main_type_items','report_data','last_date','dates','items_totals','main_type_items_totals','totals_sales_per_main_type','items_totals'));

        }
    }
}
