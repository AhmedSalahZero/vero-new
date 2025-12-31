<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Helpers\HMath;
use App\Http\Controllers\HomeController;
use App\Models\Company;
use App\Models\ExpenseAnalysis;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ExpenseAgainstAnalysisReport
{
    use GeneralFunctions;
	
	protected function formatMonthlySalesGathering(array $items){
		$result = [];
		foreach($items as $itemArr){
			$date = Carbon::make($itemArr['date'])->format('d-m-Y');
			$value = $itemArr['Sales Values'];
		
			$result[$date] = $value ;
		}
		uksort($result, 'sort_by_key_date_string'); 
		return $result;
	}
	public function viewDashboard(Company $company,Request $request)
	{
		$initialDates = getEndYearBasedOnDataUploadedFromExpense($company) ;
		$startDate = $initialDates['jan'];
		$endDate  = $initialDates['dec'];
		if($request->has('start_date')){
			$startDate = $request->get('start_date');
		}
		if($request->has('end_date')){
			$endDate = $request->get('end_date');
		}
		
		$salesGatheringSales = (new HomeController)->formatMonthlyChars($company, $startDate, $endDate);
	
		$end_date_month = date('m-Y', strtotime($endDate));
		$currentMonth = explode('-', $end_date_month)[0];
		$currentYear = explode('-', $end_date_month)[1];
		
		$currentMonthExpenses = DB::select(DB::raw(

			"
            select sum(total_cost) as current_month_expenses from expense_analysis where year(date) = " . $currentYear . " and month(date)=" . $currentMonth . " and company_id = 
            " . $company->id
		));

		$currentMonthExpenses = $currentMonthExpenses[0]->current_month_expenses ?: 0;
		$previousMonth = Carbon::make($endDate)->startOfMonth()->subMonths(1)->month;
		$previousMonthYear = Carbon::make($endDate)->startOfMonth()->subMonths(1)->year;
		$previous2Month = Carbon::make($endDate)->startOfMonth()->subMonths(2)->month;
		$previous2MonthYear = Carbon::make($endDate)->startOfMonth()->subMonths(2)->year;
		$previous3Month = Carbon::make($endDate)->startOfMonth()->subMonths(3)->month;
		$previous3MonthYear = Carbon::make($endDate)->startOfMonth()->subMonths(3)->year;
		
		$perviousMonthExpenses = DB::select(DB::raw(
			"
            select sum(total_cost) as previous_month_expenses from expense_analysis where year(date) = " . $previousMonthYear . " and month(date)=" . $previousMonth . " and company_id = 
            " . $company->id
		));
		
		$previousMonthExpenses = $perviousMonthExpenses[0]->previous_month_expenses;
		$percentage = $previousMonthExpenses && $previousMonthExpenses != 0 ? ((($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100)   : 0;
	
		$monthlySalesForSalesGathering = $salesGatheringSales['formattedDataForChart']??[];
		$monthlySalesForSalesGathering=$this->formatMonthlySalesGathering($monthlySalesForSalesGathering);
		$thirdColumnData = [];
		$result = DB::table('expense_analysis')->where('company_id',$company->id)->whereBetween('date',[$startDate,$endDate])->pluck('category_name','expense_name')->toArray();
		$firstColumnData = array_values(array_unique(array_values($result)));
		$thirdColumnData = array_values(array_keys($result));
		$salesToDate = DB::select(DB::raw(
			"select sum(net_sales_value) total_sales_to_date from sales_gathering where date >= '" . $startDate . "' and date <= '" . $endDate . "' and company_id = " . $company->id
		));
		$expensesToDate = DB::select(DB::raw(
			"select sum(total_cost) total_sales_to_date from expense_analysis where date >= '" . $startDate . "' and date <= '" . $endDate . "' and company_id = " . $company->id
		));
		$expensesToDate = $expensesToDate[0]->total_sales_to_date ?: 0;
		$avgMinMaxOutliersRequest = (new Request())->merge([
			'type'=>'category_name',
			'firstColumnName'=>'category_name',
			'data_type'=>'value',
			'report_type'=>'comparing',
			'start_date'=>$startDate,
			'end_date'=>$endDate,
			'interval'=>null,
			'start_date_second'=>$startDate,
			'end_date_second'=>$endDate,
			'tableName'=>'expense_analysis',
			'reportSelectorType'=>'one_selector',
			'firstColumnData'=>$firstColumnData
		]);
		$avgMinMaxView = $this->AvgMinMaxReportResult($avgMinMaxOutliersRequest,$company,'array',true) ;
		
		$avgMinMaxOutliers = is_array($avgMinMaxView) ?$avgMinMaxView: $avgMinMaxView->getData();
		$avgMinMaxOutliers = $avgMinMaxOutliers['firstReportData']['report_data'] ?? []; 
		$salesToDate = $salesToDate[0]->total_sales_to_date ?: 0;
		$request = (new Request())->merge([
			'reportSelectorType'=>'three_selector',
			'firstColumnName'=>'category_name',
			'secondColumnName'=>'sub_category_name',
			'thirdColumnName'=>'expense_name',
			'type'=>'expense_name',
			'start_date'=>$startDate,
			'end_date'=>$endDate,
			'report_type'=>'trend',
			'interval'=>'monthly',
			'firstColumnData'=>$firstColumnData,
			'thirdColumnData'=>$thirdColumnData,
			
		]);
		$result = $this->twoSelectorAndThreeSelectorAndComparingResult($request,$company,'array',true);
		
		$perviousThreeMonthsExpenses = DB::select(DB::raw(
			"select sum(total_cost ) previous_three_months_expenses from expense_analysis 
            where (
            (year(date)  =  " . $previousMonthYear  . " and month(date)=  " . $previousMonth . " ) 
            OR 
            (year(date)  = " . $previous2MonthYear  . " and month(date)= " . $previous2Month . ") 
            OR 
            (year(date)  = " .  $previous3MonthYear  . " and month(date)= " . ($previous3Month) . ") 
            )
                and company_id = " . $company->id
		));

		$perviousThreeMonthsExpenses = $perviousThreeMonthsExpenses[0]->previous_three_months_expenses ?: 0;
		$yearOfEndDate = Carbon::make($endDate)->startOfMonth()->subMonth(1)->format('Y') ;

		return view('admin.dashboard.expense',[
			'startDate'=>$startDate,
			'endDate'=>$endDate,
			'result'=>$result,
			'totalSales'=>$salesToDate,
			'avgMinMaxOutliers'=>$avgMinMaxOutliers,
			'monthlySalesForSalesGathering'=>$monthlySalesForSalesGathering,
			'currentMonthExpenses'=>$currentMonthExpenses,
			'percentage'=>$percentage,
			'expensesToDate'=>$expensesToDate,
			'perviousThreeMonthsExpenses'=>$perviousThreeMonthsExpenses,
			'previousMonthExpenses'=>$previousMonthExpenses,
			'yearOfEndDate'=>$yearOfEndDate
		]);
	}
	/**
	 * * هنا عندنا 3 اشكال 
	 * * اما يكون سلكت واحد وبالتالي مفيش حته المقارنة
	 * * اما يكون عندك اتنين سلكت التاني بيعتمد علي الاول 
	 * * او يكون عندك ثلاث سلكت بيعتمد علي اللي قبله 
	 * * 
	 */
    public function viewOneAndTwoSelectorAndThreeSelectorAndComparing(Company $company,string $firstColumn , string $secondColumn = null , string $thirdColumn = null)
	{
			$tableName = 'expense_analysis';
			$reportSelectorType = 'three_selector';
			$lastColumnName = $thirdColumn;
			if(is_null($secondColumn)&&is_null($thirdColumn)){
				$reportSelectorType = 'one_selector';
				$lastColumnName = $firstColumn;
			}
			elseif(is_null($thirdColumn)){
				$reportSelectorType = 'two_selector';
				$lastColumnName = $secondColumn;
			}
	
			$classesBasedOnSelectorCount = [
				'one_selector'=>[
					'data_type'=>'col-md-2',
					'report_type'=>'hidden',
					'start_date'=>'col-md-2',
					'end_date'=>'col-md-2',
					'interval'=>'col-md-2',
					'first_selector'=>'col-md-4',
					'second_selector'=>'hidden',
					'third_selector'=>'hidden',
				],
				'two_selector'=>[
					'data_type'=>'col-md-6',
					'report_type'=>'col-md-6',
					'start_date'=>'col-md-4',
					'end_date'=>'col-md-4',
					'interval'=>'col-md-4',
					'first_selector'=>'col-md-6',
					'second_selector'=>'col-md-6',
					'third_selector'=>'hidden',
				],
				'three_selector'=>[
					'data_type'=>'col-md-6',
					'report_type'=>'col-md-6',
					'start_date'=>'col-md-4',
					'end_date'=>'col-md-4',
					'interval'=>'col-md-4',
					'first_selector'=>'col-md-4',
					'second_selector'=>'col-md-4',
					'third_selector'=>'col-md-4',
				],
				][$reportSelectorType];
	
				$submitRouteName = [
					'one_selector'=>route('one.selector.expense.report.result',['company'=>$company->id ]) ,
					'two_selector'=>route('result.expense.against.report',['company'=>$company->id ]),
					'three_selector'=>route('result.expense.against.report',['company'=>$company->id ]),
				][$reportSelectorType];
            $type = $firstColumn;
			$firstColumnViewName = formatTitle($firstColumn) ;
			$secondColumnViewName = formatTitle($secondColumn)  ;
			$thirdColumnViewName = formatTitle($thirdColumn)  ;
            $view_name = $firstColumnViewName . ' Against '. $secondColumnViewName .' Expense Analysis Report' ;
			$firstColumnData =  getExpenseFor($firstColumn , $company->id , false ) ;
			$isComparingReport = $reportSelectorType != 'one_selector';
        return view('client_view.reports.sales_gathering_analysis.expense-against-report', compact('company','submitRouteName','lastColumnName','isComparingReport','thirdColumnViewName','type','view_name','firstColumnData','reportSelectorType','classesBasedOnSelectorCount','firstColumn','secondColumn','thirdColumn','tableName','firstColumnViewName','secondColumnViewName','reportSelectorType'));
    }

    public function twoSelectorAndThreeSelectorAndComparingResult(Request $request, Company $company,$result='view' , $secondReport = true )
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
		$reportSelectorType = $request->get('reportSelectorType');
        $report_data =[];
        $report_data_quantity =[];
        $growthRates =[];
        $final_report_total =[];
        $first_columns_names = [];
        $firstColumnItems = $request->get('firstColumnData',[]) ;
        $lastColumnName = $request->type ;
		$firstColumn = $request->get('firstColumnName');
		$secondColumnName = $request->get('secondColumnName');
		$thirdColumnName = $request->get('thirdColumnName');
		$type = $lastColumnName;
        $name_of_report_item  = ($result=='view') ? 'Sales Values' : 'Avg. Prices';
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'total_cost' : 'quantity';
		
		$firstColumnViewName = ucwords(str_replace('_',' ',$firstColumn));
		$secondColumnViewName = ucwords(str_replace('_',' ',$secondColumnName));
		$thirdColumnViewName = ucwords(str_replace('_',' ',$thirdColumnName));
		$lastColumnQuery  = ','.$lastColumnName ;
		$dataForLastItem = [];
		$view_name='';
		if($reportSelectorType == 'three_selector'){
			$dataForLastItem = $request->thirdColumnData??[] ;
			$view_name = $firstColumnViewName .' '. __('Against') . ' '  . $thirdColumnViewName . ' ' . __('Analysis Report');
		}elseif($reportSelectorType == 'two_selector'){
			$dataForLastItem = $request->secondColumnData??[] ;
			$view_name = $firstColumnViewName.' ' . __('Against') . ' '  . $secondColumnViewName . ' ' . __('Analysis Report');
		}
		$whereIn = '';
        foreach ($firstColumnItems as  $firstColumnItem) {

                $results =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$data_type." ,".$firstColumn.$lastColumnQuery ."
                    FROM expense_analysis
                    WHERE ( company_id = '".$company->id."'AND ". $firstColumn ."  = '".$firstColumnItem."' AND date between '".$request->start_date."' and '".$request->end_date."')
					". $whereIn ."
                    ORDER BY id "
                    )))->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){
                            return $sub_item->sum($data_type);
                        });
                    })->toArray();
			
            foreach (($dataForLastItem) as $second_column_key => $second_column) {

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
	public function oneSelectorResult(Request $request, Company $company , $array=false )
    {
		$monthlySalesReport = (new salesReport)->result($request,$company,'only_data');

        $dimension = $request->report_type;
		$firstColumnName = $request->get('type');
        $report_data =[];
        $growthRates =[];
        $mainItems = $request->get('firstColumnData',[]) ;
        foreach ($mainItems as  $currentMainItemName) {
            $currentResult =collect(DB::select(DB::raw("
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , total_cost ,".$firstColumnName."
                FROM expense_analysis
                WHERE ( company_id = '".$company->id."'AND ".$firstColumnName." = '".$currentMainItemName."' AND date between '".$request->start_date."' and '".$request->end_date."')
                ORDER BY id "
                )))->groupBy('gr_date')->map(function($item){
                    return $item->sum('total_cost');
                })->toArray();
		
            $interval_data_per_item = [];
            $years = [];
            if (count($currentResult)>0) {
                array_walk($currentResult, function ($val, $date) use (&$years) {
                    $years[] = date('Y', strtotime($date));
                });
                $years = array_unique($years);
                $report_data[$currentMainItemName] = $currentResult;
                $interval_data_per_item[$currentMainItemName] = $currentResult;
			
                $interval_data = Intervals::intervalsWithoutDouble($request->get('end_date'),$interval_data_per_item, $years, $request->interval,'multi','intervals_summation',true,true,true,explode('-',$request->get('start_date'))[1]);
			
                $report_data[$currentMainItemName] = $interval_data['data_intervals'][$request->interval][$currentMainItemName] ?? [];
                $growthRates[$currentMainItemName] = $this->growthRate($report_data[$currentMainItemName]);
            }
        }
	

        $mainItemTotals = $this->finalTotal($report_data);
        $totalGrowthRates =  $this->growthRate($mainItemTotals);
        $final_report_data = [];
        $names =[];
        foreach ($mainItems as  $currentMainItemName) {
            $final_report_data[$currentMainItemName]['Sales Values'] = ($report_data[$currentMainItemName]??[]);
            $final_report_data[$currentMainItemName]['Growth Rate %'] = ($growthRates[$currentMainItemName]??[]);
            $names[] = (str_replace( ' ','_', $currentMainItemName));
        }
        if($array)
        {
            return $report_data;
        }
		$dates = array_keys($mainItemTotals ?? []) ;
		$firstColumnNameTitle = formatTitle($firstColumnName) ;
		$reportTitle = $firstColumnNameTitle .' '. 'Trend Analysis';
		$final_report_data = HArr::getKeysSortedDescByKey($final_report_data,'Sales Values');
		$intervalName = $request->get('interval');
		$intervalName = capitializeType($intervalName);
		$percentageTableTitle = $firstColumnNameTitle . ' ' . __('(%) Against Total '. $intervalName .' Expense Value');
		$intervalPercentageTitle = $firstColumnNameTitle . ' ' . __('(%) Percentage Of Total Expense Value');
		$salesReportForInterval = sumIntervals($monthlySalesReport,$request->interval);
	
        return view('client_view.reports.sales_gathering_analysis.expense-single-selector-report',compact('company','percentageTableTitle','salesReportForInterval','intervalPercentageTitle','firstColumnNameTitle','reportTitle','names','totalGrowthRates','final_report_data','mainItemTotals','dates'));

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
	public function viewBreakdownReport(Request $request,Company $company,string $columnName)
	{
		$viewName = formatTitle($columnName) . ' Breakdown Analysis';
		$submitRoute = route('result.expense.breakdown.report',['company'=>$company->id]);
		return view('client_view.reports.sales_gathering_analysis.expense-breakdown-form',['company'=>$company,'view_name'=>$viewName,'type'=>$columnName,'submitRoute'=>$submitRoute]);
	}
	public function breakdownResult(Request $request, Company $company, $result = 'view')
	{
		$simpleLinearRegressionData =[];
		$predictionArr = [];
		$breakdownStartDate = $request->start_date ;
		$breakdownEndDate = $request->end_date ;
		$numOfTop = 100;
	
		
		$predictionForMonthArr =[];
		$report_data = [];
		$report_view_data = [];
		$growth_rate_data = [];
		$report_count_data = [];

		$request->merge([
			'type'=>$request->get('type') == 'day' ? 'day_name':$request->get('type')
		]);
		$type = $request->type;
		$dates = [
			'start_date' => date('d-M-Y', strtotime($request->start_date)),
			'end_date' => date('d-M-Y', strtotime($request->end_date))
		];


		
		
		$salesToDate = DB::select(DB::raw(
			"select sum(net_sales_value) total_sales_to_date from sales_gathering where date >= '" . $breakdownStartDate . "' and date <= '" . $breakdownEndDate . "' and company_id = " . $company->id
		));
		$salesToDate = $salesToDate[0]->total_sales_to_date ?: 0;
		
		$view_name = $request->view_name;
		
		$report_data =  collect(DB::select(DB::raw(
			"
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , total_cost," . $type . "
                FROM expense_analysis
                WHERE ( company_id = '" . $company->id . "'AND " . $type . " IS NOT NULL  AND date between '" . $breakdownStartDate . "' and '" . $breakdownEndDate . " ')
                ORDER BY id "
		)));
		
			$key_num = 0;
			$others = 0;
			$report_data = $report_data->groupBy($type)->flatMap(function ($item, $name)  {
			
				return   [[
					'item' => $name,
					'Sales Value' => $item->sum('total_cost')
					]];
				})->toArray();
			

		

		if ((count($report_data) > 0) && ($type !== 'service_provider_birth_year') && $result !== "withOthers") {
			$key_num = 0;
			$report_data =  collect($report_data)->sortByDesc(function ($data, $key) use ($key_num) {
				return [($data['Sales Value'])];
			});
			$viewing_data = $report_data->toArray();
			$total_of_all_data = array_sum(array_column($viewing_data, 'Sales Value'));
			if ($request->get('direction') == 'asc') {
				$report_data  = $report_data->reverse();
			}
			
			$top_50 = $report_data->take($numOfTop);


			$others_count = count($report_data) - count($top_50);
			$report_view_data = $top_50->toArray();
			$others_total = $total_of_all_data - array_sum(array_column($report_view_data, 'Sales Value'));
			if ($others_total > 0) {
				array_push($report_view_data, ['item' => 'Others ' . $others_count, 'Sales Value' => $others_total]);
			}
			$key_num = 0;
			$final_data = [];

			array_walk($report_view_data, function ($value, $key) use (&$key_num, &$final_data) {
				$final_data[$key_num] = $value;
				$key_num++;
			});
			$report_view_data = $final_data;
		}
		
		if($result == 'array'){
	
			return $report_view_data;
		}
	
			if (count($report_data) == 0) {
				toastr()->error('No Data Found');
				return redirect()->back();
			}
			$last_date = null;
			// Last Date
			$last_date = DB::table('expense_analysis')->where('company_id', $company->id)->latest('date')->first()->date;
			$last_date = date('d-M-Y', strtotime($last_date));
			
			return view('client_view.reports.sales_gathering_analysis.expense-breakdown-result', compact('last_date','salesToDate', 'report_count_data', 'type', 'view_name', 'dates', 'company', 'report_view_data'));
	}
	public function viewAvgMinMaxReport(Company $company,string $firstColumn , string $secondColumn = null , string $thirdColumn = null)
	{
			$tableName = 'expense_analysis';
			$reportSelectorType = 'three_selector';
			$lastColumnName = $thirdColumn;
			if(is_null($secondColumn)&&is_null($thirdColumn)){
				$reportSelectorType = 'one_selector';
				$lastColumnName = $firstColumn;
			}
			elseif(is_null($thirdColumn)){
				$reportSelectorType = 'two_selector';
				$lastColumnName = $secondColumn;
			}
	
			$classesBasedOnSelectorCount = [
				'one_selector'=>[
					'data_type'=>'col-md-6',
					'report_type'=>'col-md-6',
					'start_date'=>'col-md-4',
					'end_date'=>'col-md-4',
					'interval'=>'col-md-2',
					'first_selector'=>'col-md-4',
					'second_selector'=>'hidden',
					'third_selector'=>'hidden',
				],
				'two_selector'=>[
					'data_type'=>'col-md-6',
					'report_type'=>'col-md-6',
					'start_date'=>'col-md-4',
					'end_date'=>'col-md-4',
					'interval'=>'col-md-4',
					'first_selector'=>'col-md-6',
					'second_selector'=>'col-md-6',
					'third_selector'=>'hidden',
				],
				'three_selector'=>[
					'data_type'=>'col-md-6',
					'report_type'=>'col-md-6',
					'start_date'=>'col-md-4',
					'end_date'=>'col-md-4',
					'interval'=>'col-md-4',
					'first_selector'=>'col-md-4',
					'second_selector'=>'col-md-4',
					'third_selector'=>'col-md-4',
				],
				][$reportSelectorType];
	
				$submitRouteName = [
					'one_selector'=>route('result.avg.min.max.against.report',['company'=>$company->id ]) ,
					'two_selector'=>route('result.avg.min.max.against.report',['company'=>$company->id ]),
					'three_selector'=>route('result.avg.min.max.against.report',['company'=>$company->id ]),
				][$reportSelectorType];
            $type = $firstColumn;
			$firstColumnViewName = formatTitle($firstColumn)  ;
			$secondColumnViewName = formatTitle($secondColumn) ;
			$thirdColumnViewName =formatTitle($thirdColumn)  ;
			if($reportSelectorType == 'three_selector'){
				$view_name =  $thirdColumnViewName.' '. __('Average Min Max & Outliers Values Report');
			}elseif($reportSelectorType == 'two_selector'){
				$view_name =  $secondColumnViewName . ' ' . __('Average Min Max & Outliers Values Report');
			}else{
				$view_name =  $firstColumnViewName . ' ' . __('Average Min Max & Outliers Values Report');
			}
			
       
			$firstColumnData =  getExpenseFor($firstColumn , $company->id , false ) ;
			$isComparingReport = $reportSelectorType != 'one_selector';
			$isComparingReport = true ;
        return view('client_view.reports.sales_gathering_analysis.expense-avg-min-max-report', compact('company','submitRouteName','lastColumnName','isComparingReport','thirdColumnViewName','type','view_name','firstColumnData','reportSelectorType','classesBasedOnSelectorCount','firstColumn','secondColumn','thirdColumn','tableName','firstColumnViewName','secondColumnViewName','reportSelectorType'));
    }
		
	public function AvgMinMaxReportResult(Request $request, Company $company,$result='view' , $secondReport = true )
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
		$reportSelectorType = $request->get('reportSelectorType');
        $report_data =[];
		$avgMinMaxArr = [];
        $report_data_quantity =[];
        $growthRates =[];
        $final_report_total =[];
        $first_columns_names = [];
        $firstColumnItems = $request->get('firstColumnData',[]) ;
        $lastColumnName = $request->type ;
		$secondColumnName = $request->get('secondColumnName');
		$thirdColumnName = $request->get('thirdColumnName');
		$reportSelectorType = $request->get('reportSelectorType');
		$type = $lastColumnName;
        $name_of_report_item  = ($result=='view') ? 'Sales Values' : 'Avg. Prices';
        $data_type = ($request->data_type === null || $request->data_type == 'value')? 'total_cost' : 'quantity';
		$firstColumn = $request->get('firstColumnName');
		
		$firstColumnViewName = ucwords(str_replace('_',' ',$firstColumn));
		$secondColumnViewName = ucwords(str_replace('_',' ',$secondColumnName));
		$thirdColumnViewName = ucwords(str_replace('_',' ',$thirdColumnName));
		$lastColumnQuery  = ','.$lastColumnName ;
		$dataForLastItem = [];
		$view_name ='';
		if($reportSelectorType == 'three_selector'){
			$dataForLastItem = $request->thirdColumnData??[] ;
			$view_name =  $thirdColumnViewName.' '. __('Average Min Max & Outliers Values Report');
		}elseif($reportSelectorType == 'two_selector'){
			$dataForLastItem = $request->secondColumnData??[] ;
			$view_name =  $secondColumnViewName . ' ' . __('Average Min Max & Outliers Values Report');
		}else{
			$view_name =  $firstColumnViewName . ' ' . __('Average Min Max & Outliers Values Report');
			$dataForLastItem = $request->input('firstColumnData',[]);
		}
		$whereIn = '';
        foreach ($firstColumnItems as  $firstColumnItem) {

      
                $results =collect(DB::select(DB::raw("
                    SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , ".$data_type." ,".$firstColumn.$lastColumnQuery ."
                    FROM expense_analysis
                    WHERE ( company_id = '".$company->id."'AND ". $firstColumn ."  = '".$firstColumnItem."' AND date between '".$request->start_date."' and '".$request->end_date."')
					". $whereIn ."
                    ORDER BY id "
                    )))->groupBy($type)->map(function($item)use($data_type){
                        return $item->groupBy('gr_date')->map(function($sub_item)use($data_type){
                            return $sub_item->sum($data_type);
                        });
                    })->toArray();
					
        
            foreach (($dataForLastItem) as $second_column_key => $second_column) {
				$years = [];
                $data_per_main_item = $results[$second_column]??[];
                if (count(($data_per_main_item))>0 ) {
                    // Data & Growth Rate Per Sales Channel
                    array_walk($data_per_main_item, function ($val, $date) use (&$years) {
                        $years[] = date('Y', strtotime($date));
                    });
                    $years = array_unique($years);
                   
					$counts = count($data_per_main_item) ; 
				
					$report_data[$firstColumnItem.' - '.$second_column]['Average Value'] = $counts ? array_sum($data_per_main_item ) / $counts : 0 ;
					$report_data[$firstColumnItem.' - '.$second_column]['Min Value'] = $counts? HArr::getMinValuesWithItsDate($data_per_main_item) : [] ;
					$report_data[$firstColumnItem.' - '.$second_column]['Min Value']['only_date_modal'] =1 ;
					$report_data[$firstColumnItem.' - '.$second_column]['Max Value'] = $counts ? HArr::getMaxValuesWithItsDate($data_per_main_item) : [] ;
					$report_data[$firstColumnItem.' - '.$second_column]['Max Value']['only_date_modal'] = 1 ;
					$report_data[$firstColumnItem.' - '.$second_column]['Outliers']['dates'] = $counts ? HMath::removeOutliers($data_per_main_item) : [] ;
					$report_data[$firstColumnItem.' - '.$second_column]['Outliers']['date_and_value_modal'] = 1 ;
                }
            }
            // Total & Growth Rate Per Zone
            $first_columns_names[] = (str_replace( ' ','_', $firstColumnItem));
        }
		foreach($report_data as $r=>$d){
			unset($report_data[$r]['Totals']);
        }
		$dates = [];
        $Items_names = $first_columns_names ;
         $report_view = getComparingReportForAnalysis($request , $report_data , $secondReport , $company , $dates , $view_name , $Items_names , $firstColumn,true );
	
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
	public function viewIntervalComparingReport(Request $request,Company $company,$type ){
		$firstColumnViewName = formatTitle($type) ;
		$view_name = $firstColumnViewName . ' ' . __('Interval Comparing');
		$submitRoute = route('result.interval.comparing.report',['company'=>$company->id]);
		return view('client_view.reports.sales_gathering_analysis.view-interval-comparing-expenses', compact('company', 'view_name','type','submitRoute'));
	}
	
	public function resultIntervalComparingReport(Request $request,Company $company ){
		$start_date_one  = $request->start_date_one;
        $end_date_one  = $request->end_date_one;
        $start_date_two  = $request->start_date_two;
        $end_date_two  = $request->end_date_two;
        $type = $request->type ;
        $view_name = $request->view_name ;
        $count_result_for_interval_one = [];
        $count_result_for_interval_two = [];
        $dates = [
            'start_date_one' => date('d-M-Y',strtotime($start_date_one)),
            'end_date_one' => date('d-M-Y',strtotime($end_date_one)),
            'start_date_two' => date('d-M-Y',strtotime($start_date_two)),
            'end_date_two' => date('d-M-Y',strtotime($end_date_two)),
        ];
		$latestReport = null ;
		if(Carbon::make($end_date_two)->greaterThan(Carbon::make($end_date_one))){
			$latestReport =    '_two' ;
		}elseif(Carbon::make($end_date_one)->greaterThan(Carbon::make($end_date_two))){
			$latestReport =    '_one' ;
		}
		
		

        // First_interval
        $request['start_date']=$start_date_one;
        $request['end_date']=$end_date_one;
		
		$salesToDateForFirstInterval = DB::select(DB::raw(
			"select sum(net_sales_value) total_sales_to_date from sales_gathering where date >= '" . $start_date_one . "' and date <= '" . $end_date_one . "' and company_id = " . $company->id
		));
		$salesToDateForFirstInterval = $salesToDateForFirstInterval[0]->total_sales_to_date ?: 0;
		
		
	
		
        $result_for_interval_one = (new ExpenseAgainstAnalysisReport)->breakdownResult($request,$company,'array');


        if (isset($result_for_interval_one['report_count_data'])) {
            $count_result_for_interval_one = $result_for_interval_one['report_count_data'];
            $result_for_interval_one = $result_for_interval_one['report_view_data'];
        }
        // Second_interval
        $request['start_date']=$start_date_two;
        $request['end_date']=$end_date_two;
		
		
		$salesToDateForSecondInterval = DB::select(DB::raw(
			"select sum(net_sales_value) total_sales_to_date from sales_gathering where date >= '" . $start_date_two . "' and date <= '" . $end_date_two . "' and company_id = " . $company->id
		));
		$salesToDateForSecondInterval = $salesToDateForSecondInterval[0]->total_sales_to_date ?: 0;
		$salesToDateForIntervals = [
			'_one'=>$salesToDateForFirstInterval,
			'_two'=>$salesToDateForSecondInterval,
		];

        $result_for_interval_two = (new ExpenseAgainstAnalysisReport)->breakdownResult($request,$company,'array');
		
        if (isset($result_for_interval_two['report_count_data'])) {
            $count_result_for_interval_two = $result_for_interval_two['report_count_data'];
            $result_for_interval_two = $result_for_interval_two['report_view_data'];
        }
		$last_date = null;
        $last_date = ExpenseAnalysis::company()->latest('date')->first() ? ExpenseAnalysis::company()->latest('date')->first()->date : null;
        $last_date = $last_date ? date('d-M-Y',strtotime($last_date)) : null ;

            $last_date = null;
            // Last Date
            $last_date = ExpenseAnalysis::company()->latest('date')->first()->date;
            $last_date = date('d-M-Y',strtotime($last_date));
            
            return view('client_view.reports.sales_gathering_analysis.result-interval-comparing-expenses',compact('last_date','type','view_name','dates','company','result_for_interval_one','result_for_interval_two',
   
            'count_result_for_interval_one','count_result_for_interval_two','latestReport','salesToDateForIntervals'
		
         
            ));
       
		
	}
}
