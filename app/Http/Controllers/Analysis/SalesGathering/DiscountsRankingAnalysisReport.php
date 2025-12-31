<?php
namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Models\Company;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountsRankingAnalysisReport
{
    use GeneralFunctions;
    public function index(Request $request, Company $company)
    {
        if (request()->route()->named('zone.vs.discounts.view')) {
            $main_type = 'zone';
            $type = 'discounts';
            $view_name = 'Zones Versus Discounts' ;
        }
        elseif (request()->route()->named('salesChannels.vs.discounts.view')) {
            $main_type = 'sales_channel';
            $type = 'discounts';
            $view_name = 'Sales Channels Versus Discounts' ;
        }
        elseif (request()->route()->named('categories.vs.discounts.view')) {
            $main_type = 'category';
            $type = 'discounts';
            $view_name = 'Categories Versus Discounts' ;
        }
        elseif (request()->route()->named('products.vs.discounts.view')) {
            $main_type = 'product_or_service';
            $type = 'discounts';
            $view_name = 'Products Versus Discounts' ;
        }
        elseif (request()->route()->named('Items.vs.discounts.view')) {
            $main_type = 'product_item';
            $type = 'discounts';
            $view_name = 'Products Items Versus Discounts' ;
        }
        elseif (request()->route()->named('businessSectors.vs.discounts.view')) {
            $main_type = 'business_sector';
            $type = 'discounts';
            $view_name = 'Business Sectors Versus Discounts' ;
        }elseif (request()->route()->named('businessUnits.vs.discounts.view')) {
            $main_type = 'business_unit';
            $type = 'discounts';
            $view_name = 'Business Units Versus Discounts' ;
        }
        elseif (request()->route()->named('branches.vs.discounts.view')) {
            $main_type = 'branch';
            $type = 'discounts';
            $view_name = 'Branches Versus Discounts' ;
        }
        elseif (request()->route()->named('principles.vs.discounts.view')) {
            $main_type = 'principle';
            $type = 'discounts';
            $view_name = 'Principles Versus Discounts' ;
        }
        elseif (request()->route()->named('customers.vs.discounts.view')) {
            $main_type = 'customer_name';
            $type = 'discounts';
            $view_name = 'Customers Versus Discounts' ;
        }elseif (request()->route()->named('country.vs.discounts.view')) {
            $main_type = 'country';
            $type = 'discounts';
            $view_name = 'Countries Versus Discounts' ;
        }

        return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_form', compact('company', 'view_name','type','main_type'));
    }


    public function result(Request $request, Company $company,$result='view')
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


        $all_items = [
            'quantity_discount' => 'Quantity Discount' ,
            'cash_discount' => 'Cash Discount' ,
            'special_discount' => 'Special Discount' ,
            'other_discounts' => 'Other Discount' ,
        ];




        $report_all =collect(DB::select(DB::raw("
            SELECT special_discount , quantity_discount ,other_discounts ,cash_discount ,sales_value,".$main_type ."
            FROM sales_gathering
            WHERE ( company_id = '".$company->id."'AND ".$main_type." IS NOT NULL  AND date between '".$request->start_date."' and '".$request->end_date."')
            ORDER BY id "
        )))->groupBy($main_type);
        $report_data = $report_all->flatMap(function($item,$name)  {
                return [
                    $name => [
                        'Quantity Discount' => $item->sum('quantity_discount' ) ,
                        'Cash Discount' => $item->sum('cash_discount' ) ,
                        'Special Discount' => $item->sum('special_discount' ) ,
                        'Other Discount' => $item->sum('other_discounts' ) ,
                    ]
                ];
            })->toArray();

        $totals_sales_per_main_type = $report_all->flatMap(function($item,$name)  {
            return [
                $name => $item->sum('sales_value' )
            ];
        })->toArray();
        $total_sales = array_sum($totals_sales_per_main_type);
        $main_type_items = array_keys($totals_sales_per_main_type);


        $main_type_items_totals = $report_all->flatMap(function($item,$name)  {
            return [
                $name => $item->sum('quantity_discount' )+
                        $item->sum('cash_discount' ) +
                        $item->sum('special_discount' )+
                        $item->sum('other_discounts' )
            ];
        })->toArray();




        $items_totals = $this->finalTotal([$report_data]);

        arsort($main_type_items_totals);

        if(count($main_type_items_totals) > 50){
            $report_view_data = collect($main_type_items_totals);
            $top_20 = $report_view_data->take(50);
            $report_view_data = $top_20->toArray();
            $main_type_items_totals = $report_view_data;
            foreach ($report_view_data as $name_of_main_item => $data) {

                $result_data[$name_of_main_item] = $report_data[$name_of_main_item]??0;
                unset($report_data[$name_of_main_item]);
            }
            $result_data['Others '.count($report_data)] =  $this->finalTotal([$report_data]);

            $main_type_items_totals['Others '.count($report_data)]  = array_sum(($result_data['Others '.count($report_data)]??[]));
            $report_data = $result_data;
        }
        $all_items = array_unique($all_items);
        if ($result=='view') {

            $last_date = DB::table('sales_gathering')->latest('date')->first()->date;
            $last_date = date('d-M-Y',strtotime($last_date));


            return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_report',compact('company','totals_sales_per_main_type','view_name', 'main_type','type','all_items','main_type_items','report_data','last_date','dates','items_totals','total_sales','main_type_items_totals'));
        }else{

            return ['report_data'=>$report_data,'all_items'=>$all_items,'items_totals'=>$items_totals,'main_type_items_totals'=>$main_type_items_totals,'totals_sales_per_main_type' => $totals_sales_per_main_type,'total_sales'=>$total_sales];
        }

    }
}
