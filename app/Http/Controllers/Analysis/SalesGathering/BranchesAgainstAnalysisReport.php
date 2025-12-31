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

class BranchesAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {
        if (request()->route()->named('branches.zones.analysis')) {
            $type = 'zone';
            $view_name = 'Branches Against Zones Trend Analysis' ;
        } elseif (request()->route()->named('branches.customers.analysis')) {
            $type = 'customer_name';
            $view_name = 'Branches Against Customers Trend Analysis' ;
        }elseif (request()->route()->named('branches.categories.analysis')) {
            $type  = 'category';
            $view_name = 'Branches Against Categories Trend Analysis' ;
        }elseif (request()->route()->named('branches.products.analysis')) {
            $type  = 'product_or_service';
            $view_name = 'Branches Against Products / Services Trend Analysis' ;
        }elseif (request()->route()->named('branches.principles.analysis')) {
            $type  = 'principle';
            $view_name = 'Branches Against Principles Trend Analysis' ;
        }elseif (request()->route()->named('branches.Items.analysis')) {
            $type  = 'product_item';
            $view_name = 'Branches Against Products Items Trend Analysis' ;
        }elseif (request()->route()->named('branches.salesPersons.analysis')) {
            $type  = 'sales_person';
            $view_name = 'Branches Against Sales Persons Trend Analysis' ;
        }elseif (request()->route()->named('branches.salesDiscount.analysis')) {
            $type  = 'quantity_discount';
            $view_name = 'Branches Against Sales Discount Trend Analysis' ;
        }elseif (request()->route()->named('branches.businessSectors.analysis')) {
            $type  = 'business_sector';
            $view_name = 'Branches Against Business Sectors Trend Analysis' ;
        }
		elseif (request()->route()->named('branches.businessUnits.analysis')) {
            $type  = 'business_unit';
            $view_name = 'Branches Against Business Units Trend Analysis' ;
        }
		elseif (request()->route()->named('branches.salesChannels.analysis')) {
            $type  = 'sales_channel';
            $view_name = 'Branches Against Sales Channels Trend Analysis' ;
        }elseif (request()->route()->named('branches.countries.analysis')) {
            $type  = 'country';
            $view_name = 'Branches Against Countries Trend Analysis' ;
        }
		elseif (request()->route()->named('branches.day.analysis')) {
            $type  = 'day_name';
            $view_name = 'Branches Against Day Name Trend Analysis' ;
        }
        $name_of_selector_label = str_replace(['Branches Against ' ,' Trend Analysis'],'',$view_name);
        return view('client_view.reports.sales_gathering_analysis.branches_analysis_form', compact('company','name_of_selector_label','type','view_name'));
    }
    public function BranchesSalesAnalysisIndex(Company $company)
    {
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
        return view('client_view.reports.sales_gathering_analysis.branches_sales_form', compact('company', 'selected_fields'));
    }
    public function result(Request $request, Company $company , $secondReport=true)
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
        $growth_rate_data =[];
        $final_report_total =[];
        $branches_names = [];
        $branches = is_array(json_decode(($request->branches[0]))) ? json_decode(($request->branches[0])) :$request->branches ;
        $type = $request->type;
        $view_name = $request->view_name;
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'net_sales_value' : 'quantity';
        foreach ($branches as  $branchName) {

            $branches_data =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$data_type." ,branch," . $type ."
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND branch = '".$branchName."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id "
                )))->groupBy($type)->map(function($item)use($data_type){
                    return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){

                        return $sub_item->sum($data_type);
                    });
                })->toArray();
            foreach (($request->sales_channels??[]) as $branch_key => $branch) {


                $years = [];

                $data_per_main_item = $branches_data[$branch]??[];
                if (count(($data_per_main_item))>0 ) {

                    // Data & Growth Rate Per Sales Channel
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                        $years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);

                    $report_data[$branchName][$branch]['Sales Values'] = $data_per_main_item;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data[$branchName][$branch], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
                    $report_data[$branchName][$branch] = $interval_data['data_intervals'][$request->interval] ?? [];

                    $report_data[$branchName]['Total']  = $this->finalTotal([($report_data[$branchName]['Total']  ?? []) ,($report_data[$branchName][$branch]['Sales Values']??[]) ]);
                    $report_data[$branchName][$branch]['Growth Rate %'] = $this->growthRate(($report_data[$branchName][$branch]['Sales Values'] ?? []));


                }
            }
            // Total & Growth Rate Per Zone
            $final_report_total = $this->finalTotal( [($report_data[$branchName]['Total']??[]) , ($final_report_total??[]) ]);
            $report_data[$branchName]['Growth Rate %'] =  $this->growthRate(($report_data[$branchName]['Total']??[]));
            $branches_names[] = (str_replace( ' ','_', $branchName));
        }
        // Total Sales Channel & Growth Rate

        $report_data['Total'] = $final_report_total;
        $report_data['Growth Rate %']=  $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);
		
        //  $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);

         
 
		$Items_names = $branches_names ;
         $report_view = getComparingReportForAnalysis($request , $report_data , $secondReport , $company , $dates , $view_name , $Items_names , 'branch' );
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
        return view('client_view.reports.sales_gathering_analysis.branches_analysis_report',compact('company','view_name','branches_names','dates','report_data','type'));

    }

    public function resultForSalesDiscount(Request $request, Company $company)
    {

        $report_data =[];
        $final_report_data =[];
        $growth_rate_data =[];
        $zones_names = [];
        $sales_values = [];
        $sales_years = [];
        $zones = is_array(json_decode(($request->branches[0]))) ? json_decode(($request->branches[0])) :$request->branches ;
        $type = $request->type;
        $view_name = $request->view_name;
        $zones_discount = [];


        $fields ='';
        foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
            $fields .= $sales_discount_field .',';
        }


        foreach ($zones as  $zone) {

            $sales =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields ." branch
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND branch = '".$zone."' AND date between '".$request->start_date."' and '".$request->end_date."')
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
        $type_name = 'Branches';
        return view('client_view.reports.sales_gathering_analysis.sales_discounts_analysis_report',compact('company','view_name','zones_names','dates','report_data','type_name'));

    }
    public function BranchesSalesAnalysisResult(Request $request, Company $company , $array = false )
    {
        $dimension = $request->report_type;

        $report_data =[];
        $growth_rate_data =[];
		$endDate = $request->get('end_date');
        $branches = is_array(json_decode(($request->branches[0]))) ? json_decode(($request->branches[0])) :$request->branches ;

        foreach ($branches as  $branch) {

        
			
                $branches_data =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ,branch
                FROM sales_gathering
                WHERE ( company_id = '".$company->id."'AND branch = '".$branch."' AND date between '".$request->start_date."' and '".$request->end_date."')
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
        return view('client_view.reports.sales_gathering_analysis.branches_sales_report',compact('company','branches_names','total_branches_growth_rates','final_report_data','total_branches','dates'));

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
