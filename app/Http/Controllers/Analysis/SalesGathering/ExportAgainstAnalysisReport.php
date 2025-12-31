<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Company $company,string $firstColumn , string $secondColumn)
	{
            $type = $firstColumn;
			$firstColumnViewName = capitializeType(str_replace('_',' ',$firstColumn)) ;
			$secondColumnViewName = capitializeType(str_replace('_',' ',$secondColumn)) ;
			$revenueStreams = getExportFor('revenue_stream',$company->id , false);
			$currencies = getExportFor('currency',$company->id , false);
            $view_name = $firstColumnViewName . ' Against '. $secondColumnViewName .' Export Analysis Report' ;
			$firstColumnData = getExportFor($firstColumn , $company->id , false );
			$secondColumnData = getExportFor($secondColumn , $company->id , false );
        return view('client_view.reports.sales_gathering_analysis.export-against-report', compact('company','type','view_name','firstColumnData','secondColumnData','firstColumn','secondColumn','firstColumnViewName','secondColumnViewName','revenueStreams','currencies'));
    }
    // public function CategoriesSalesAnalysisIndex(Company $company)
    // {
    //     $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
    //     return view('client_view.reports.sales_gathering_analysis.first_columns_sales_form', compact('company', 'selected_fields'));
    // }
    public function result(Request $request, Company $company,$result='view' , $secondReport = true )
    {
        $report_data =[];
        $report_data_quantity =[];
        $growth_rate_data =[];
        $final_report_total =[];
        $first_columns_names = [];
        $firstColumnItems = is_array(json_decode(($request->firstColumnData[0]))) ? json_decode(($request->firstColumnData[0])) :$request->firstColumnData ;
        $secondColumnName = $request->type ;
		$type = $secondColumnName;
		
        $name_of_report_item  = ($result=='view') ? 'Sales Values' : 'Avg. Prices';
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'purchase_order_net_value' : 'quantity';
		$firstColumn = $request->get('firstColumnName');
		// $secondColumnName  = $request->get('secondColumnName');
		$firstColumnViewName = ucwords(str_replace('_',' ',$firstColumn));
		$secondColumnViewName = ucwords(str_replace('_',' ',$secondColumnName));
        $view_name = $firstColumnViewName . __('Against') . ' '  . $secondColumnViewName . ' ' . __('Export Analysis Report');
		$revenueStreams = $request->get('revenue_streams');
		$currencies = $request->get('currency');
		$revenueStreamsSql = convertArrayToSqlString($revenueStreams);
		$currenciesSql = convertArrayToSqlString($currencies);
		
		$whereIn = 'and  revenue_stream in ( '. $revenueStreamsSql .' ) and currency  in ('. $currenciesSql .')';
        foreach ($firstColumnItems as  $firstColumnItem) {

            if ($result == 'view') {
                $results =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(purchase_order_date),'%d-%m-%Y') as gr_date  , ".$data_type." ,".$firstColumn."," . $secondColumnName ."
                    FROM export_analysis
                    WHERE ( company_id = '".$company->id."'AND ". $firstColumn ."  = '".$firstColumnItem."' AND purchase_order_date between '".$request->start_date."' and '".$request->end_date."')
					". $whereIn ."
                    ORDER BY id "
                    )))->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){
                        
                            return $sub_item->sum($data_type);
                        });
                    })->toArray();
            }else{
                $results = DB::table('export_analysis')
                    ->where('company_id',$company->id)
                    ->where($firstColumn, $firstColumnItem)
                    ->whereNotNull($type)
                    ->whereBetween('purchase_order_date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(LAST_DAY(purchase_order_date),"%d-%m-%Y") as gr_date ,
                    (IFNULL('.$data_type.',0) ) as '.$data_type.' ,'.$firstColumn.',' . $type)
                    ->get()
                    ->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){
                            return $sub_item->sum($data_type);
                        });
                    })->toArray();

                     $results_quantity = DB::table('export_analysis')
                    ->where('company_id',$company->id)
                    ->where($firstColumn, $firstColumnItem)
                    ->whereNotNull($type)
                    ->whereBetween('purchase_order_date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(LAST_DAY(purchase_order_date),"%d-%m-%Y") as gr_date ,
                    (IFNULL('.$data_type.',0)  ) as '.$data_type.' , IFNULL(quantity,0) quantity,'.$firstColumn.',' . $type)
                    ->get()
                    ->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){
                            return ( $sub_item->sum('quantity') ) ;
                        });
                    })->toArray();

                }
				
            foreach (($request->secondColumnData??[]) as $second_column_key => $second_column) {

                $years = [];
                $data_per_main_item = $results[$second_column]??[];
                if (count(($data_per_main_item))>0 ) {
                    // Data & Growth Rate Per Sales Channel
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                        $years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);
                    $report_data[$firstColumnItem][$second_column][$name_of_report_item] = $data_per_main_item;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data[$firstColumnItem][$second_column], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
                    $report_data[$firstColumnItem][$second_column] = $interval_data['data_intervals'][$request->interval] ?? [];
                    $report_data[$firstColumnItem]['Total']  = $this->finalTotal([($report_data[$firstColumnItem]['Total']  ?? []) ,($report_data[$firstColumnItem][$second_column][$name_of_report_item]??[]) ]);
                    $report_data[$firstColumnItem][$second_column]['Growth Rate %'] = $this->growthRate(($report_data[$firstColumnItem][$second_column][$name_of_report_item] ?? []));

                }
            }

            if($result == 'array'){
                
                 foreach (($request->secondColumnData??[]) as $second_column_key => $second_column) {

                $years = [];

                $data_per_main_item = $results_quantity[$second_column]??[];
                if (count(($data_per_main_item))>0 ) {
                    // Data & Growth Rate Per Sales Channel
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                        $years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);

                    $report_data_quantity[$firstColumnItem][$second_column][$name_of_report_item] = $data_per_main_item;
                    $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$report_data_quantity[$firstColumnItem][$second_column], $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
                    $report_data_quantity[$firstColumnItem][$second_column] = $interval_data['data_intervals'][$request->interval] ?? [];

                    $report_data_quantity[$firstColumnItem]['Total']  = $this->finalTotal([($report_data_quantity[$firstColumnItem]['Total']  ?? []) ,($report_data_quantity[$firstColumnItem][$second_column][$name_of_report_item]??[]) ]);
                    $report_data_quantity[$firstColumnItem][$second_column]['Growth Rate %'] = $this->growthRate(($report_data_quantity[$firstColumnItem][$second_column][$name_of_report_item] ?? []));

                }
            }


            foreach($report_data as $reportType=>$dates){
                       // Baby 20
                
                if($firstColumnItem  == $reportType )
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
            $final_report_total = $this->finalTotal( [($report_data[$firstColumnItem]['Total']??[]) , ($final_report_total??[]) ]);
            $report_data[$firstColumnItem]['Growth Rate %'] =  $this->growthRate(($report_data[$firstColumnItem]['Total']??[]));
            $first_columns_names[] = (str_replace( ' ','_', $firstColumnItem));

        }
          foreach($report_data as $r=>$d){
            unset($report_data[$r]['Totals']);
        }

        
        // Total Zones & Growth Rate


        $report_data['Total'] = $final_report_total;
        $report_data['Growth Rate %']=  $this->growthRate($report_data['Total']);
        $dates = array_keys($report_data['Total']);
        //  $dates = formatDateVariable($dates , $request->start_date  , $request->end_date);
        $Items_names = $first_columns_names ;
         $report_view = getComparingReportForAnalysis($request , $report_data , $secondReport , $company , $dates , $view_name , $Items_names , $firstColumn );
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
        
        if ($result=='view') {
            return view('client_view.reports.sales_gathering_analysis.first_columns_analysis_report',compact('company','firstColumnViewName','name_of_report_item','view_name','first_columns_names','dates','report_data'));
        }else {
            return [ 'report_data'=>$report_data,'view_name'=>$view_name,'names'=> $first_columns_names];
        }


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
