<?php
namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TwodimensionalSalesBreakdownAgainstRankingAnalysisReport
{
    use GeneralFunctions;
    public function index(Request $request, Company $company)
    {
        if (request()->route()->named('branches.vs.ItemsRanking.view')) {
            $main_type = 'branch';
            $type = 'product_item';
            $view_name = 'Branches Vs Product Items Ranking'  ;
        }
		elseif (request()->route()->named('branches.vs.ProductsRanking.view')) {
            $main_type = 'branch';
            $type = 'product_or_service';
            $view_name = 'Branches Vs Products / Service Ranking'  ;
        }
		$dates = getEndYearBasedOnDataUploaded($company);
		$start_date =$dates['jan'];
		$end_date=$dates['dec'];
		 
		
        return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_ranking_form', compact('company', 'view_name','type','main_type','start_date','end_date'));
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
        // $all_items = [];

        // $main_type_items_totals = [];

        
        $report_data =collect(DB::select(DB::raw("
            SELECT DATE_FORMAT(date,'%d-%m-%Y') as date, net_sales_value ,sales_value,".$type.",".$main_type ."
            FROM sales_gathering
            WHERE ( company_id = '".$company->id."' AND ".$type." IS NOT NULL AND ".$main_type." IS NOT NULL  AND date between '".$request->start_date."' and '".$request->end_date. " ')
            ORDER BY id "
            )))->groupBy($type)->map(function($item) use($main_type){
                return $item->groupBy($main_type)->map(function($sub_item){
                    return $sub_item->sum('net_sales_value');
                });
            })->toArray();

            $data = [];
            foreach($report_data as $productName => $branchValues){
               foreach($branchValues as $branchName => $total){
				if($total != 0){
					$data[$branchName][getOrderMaxForBranch($branchName , $branchValues)][$productName] = [
						'total'=>$total ,
					] ;    
				}
               }
            }
          

        $last_date = SalesGathering::company()->latest('date')->first()->date ?? null ; 
        $last_date = date('d-M-Y',strtotime($last_date));
        // $all_items = array_unique($all_items);

        uasort($data , function($a , $b){
        return  array_sum(flatten($a)) < array_sum(flatten($b)); 
        });
        return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_ranking_report',compact('data','company','view_name', 'main_type','type'
        // , 'all_items','main_type_items','report_data',
        ,'last_date','dates',
        // 'items_totals','main_type_items_totals'
        ));

    }

    // public function handleRanking(){}


    // public function discountsResult(Request $request, Company $company)
    // {
    //     {

    //         $report_data =[];
    //         $main_type = $request->main_type;
    //         $type = 'discounts';
    //         $view_name = $request->view_name;
    //         $last_date = null;
    //         $dates = [
    //             'start_date' => date('d-M-Y',strtotime($request->start_date)),
    //             'end_date' => date('d-M-Y',strtotime($request->end_date))
    //         ];
    //         $all_items = [];
    //         $main_type_items = SalesGathering::company()->whereNotNull($main_type)->groupBy($main_type)->selectRaw($main_type)->whereBetween('date', [$request->start_date, $request->end_date])->get()->pluck($main_type)->toArray();
    //         // $items = SalesGathering::company()->whereNotNull($main_type)->groupBy($main_type)->selectRaw($main_type)->whereBetween('date', [$request->start_date, $request->end_date])->get()->pluck($main_type)->toArray();
    //         $all_items = [
    //             'quantity_discount' => 'Quantity Discount' ,
    //             'cash_discount' => 'Cash Discount' ,
    //             'special_discount' => 'Special Discount' ,
    //             'other_discounts' => 'Other Discount' ,
    //         ];
    //         $totals_sales_per_main_type = [];
    //         $total_sales = 0;
    //         foreach ($main_type_items as  $main_type_item_name) {


    //                 foreach ($all_items as  $field => $field_name) {
    //                     $sales_gatherings = SalesGathering::company()
    //                                 ->where($main_type,$main_type_item_name)
    //                                 ->whereBetween('date', [$request->start_date, $request->end_date])
    //                                 ->selectRaw('DATE_FORMAT(date,"%d-%m-%Y") as date,sales_value,'.$field.','.$main_type)
    //                                 ->get();

    //                     $field_total = collect($sales_gatherings)->sum('sales_value');
    //                     $total_sales += $field_total ;
    //                     $totals_sales_per_main_type[$main_type_item_name] =  $field_total;
    //                     $main_type_items_per_month = [];
    //                     $main_type_items_data = [];

    //                     $total = collect($sales_gatherings)->sum($field);

    //                     $report_data[$main_type_item_name][$field_name] =$total;


    //                 }


    //             $main_type_items_totals[$main_type_item_name] = array_sum($report_data[$main_type_item_name]??[]);
    //         }

    //         $items_totals = $this->finalTotal([$report_data]);
    //         arsort($main_type_items_totals);


    //         if(count($main_type_items_totals) > 50){
    //             $report_view_data = collect($main_type_items_totals);
    //             $top_20 = $report_view_data->take(50);
    //             $report_view_data = $top_20->toArray();
    //             $main_type_items_totals = $report_view_data;
    //             foreach ($report_view_data as $name_of_main_item => $data) {
    //                 $result[$name_of_main_item] =$report_data[$name_of_main_item];
    //                 unset($report_data[$name_of_main_item]);
    //             }
    //             $result['Others '.count($report_data)] =  $this->finalTotal([$report_data]);

    //             $main_type_items_totals['Others '.count($report_data)]  = array_sum(($result['Others '.count($report_data)]??[]));
    //             $report_data = $result;
    //         }

    //         $last_date = SalesGathering::company()->latest('date')->first()->date;
    //         $last_date = date('d-M-Y',strtotime($last_date));
    //         $all_items = array_unique($all_items);

    //         return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_report',compact('company','view_name', 'main_type','type','all_items','main_type_items','report_data','last_date','dates','items_totals','main_type_items_totals','totals_sales_per_main_type','items_totals'));

    //     }
    // }
}
