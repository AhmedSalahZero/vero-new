<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Models\Company;
use App\Models\Log;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;

class salesReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {
		Log::storeNewLogRecord('enterSection',null,__('Sales Report'));
		
        return view('client_view.reports.sales_gathering_analysis.sales_report.sales_form', compact('company'));
    }
	
	
    public function result(Request $request, Company $company,$result='view')
    {
        // enhanced in sales dashboard // salah
        $report_data = [];
        $growth_rate_data = [];

        $dates = [];
        $gr = [];
        $last_date = null;
        $request_dates = [
            'start_date' => date('d-M-Y',strtotime($request->start_date)),
            'end_date' => date('d-M-Y',strtotime($request->end_date))
        ];
        $data = [];

        $main_data = SalesGathering::company()
                                    ->whereBetween('date', [$request->start_date, $request->end_date])
                                    // ->limit(10)
                                    ->selectRaw('DATE_FORMAT(LAST_DAY(date),"%d-%m-%Y") as gr_date,DATE_FORMAT(date,"%Y") as year,net_sales_value')->orderBy('date')
                                    ->get();
          
        if ($request->report_type == 'comparing') {
            $data = $main_data->groupBy('year')->map(function($year){
                            return $year->groupBy('gr_date')->map(function($sub_item){
                                return $sub_item->sum('net_sales_value');
                            });
                        })->toArray();
            count($data)>0 ? ksort($data) : '';

            $year_number = 1;
            $previous_data = [];
            foreach ($data as $year => $data_per_year) {
                $data_per_year = $data_per_year??[];
                $report_data[$year]['Months'] = array_keys($data_per_year);
                $report_data[$year]['Sales Values'] = $data_per_year;
                $report_data[$year]['Month Sales %'] = $this->operationAmongArrayAndNumber($data_per_year,array_sum(($data_per_year??[])));
                $report_data[$year]['Month Sales %'] = $this->operationAmongArrayAndNumber($report_data[$year]['Month Sales %'] ,100 ,'multiply');
                if ($year_number == 1) {
                    $report_data[$year]['YoY GR%'] = array_fill_keys(array_keys($data_per_year),0);
                }else{
                    $report_data[$year]['YoY GR%'] = $this->growthRatePerMonth($data_per_year,$previous_data,$year);
                }
                $year_number++;
                $previous_data = $data_per_year;
            }
            $totals = $this->finalTotal(array_column($report_data,'Sales Values')??[]);
            $total_full_data = $this->monthsTotal($totals);
        }else{
            $data = $main_data->groupBy('gr_date')->map(function($item)
                    { return $item->sum('net_sales_value'); })->toArray();


            if (count($data) > 0) {

                $report_data['Sales Values'] =  $this->sorting($data);

                $gr = $this->growthRate($data);

                $total  = array_sum($data);

                $report_data['Month Sales %'] = $this->operationAmongArrayAndNumber($report_data['Sales Values'],$total);
                $report_data['Month Sales %'] = $this->operationAmongArrayAndNumber($report_data['Month Sales %'] ,100 ,'multiply');

                $dates = array_keys($report_data['Sales Values']);
                $last_date = SalesGathering::company()->latest('date')->first()->date;
                $last_date = date('d-M-Y',strtotime($last_date));

            }
        }


		if($result == 'only_data'){
			return $data;
		}

        // Alert No Data
        if ($result == 'view' && count($data) == 0)  {

            toastr()->error('No Data Found');
            return redirect()->back();
        }
        // View
        if ($result == 'view') {
            if ($request->report_type == 'comparing') {

                return view('client_view.reports.sales_gathering_analysis.sales_report.comparing_sales_report',
                            compact('company',
                                    'total_full_data',
                                    'gr',
                                    'request_dates',
                                    'last_date',
                                    'dates',
                                    'report_data'));
            }else{
                return view('client_view.reports.sales_gathering_analysis.sales_report.sales_report', compact('company','gr','request_dates','last_date',  'dates', 'report_data'));
            }
        }
        // Interval Comparing Sales Report
        elseif ($result == 'array' && $request->report_type == 'comparing')  {
            return [
                'total_full_data' =>$total_full_data,
                'report_data' =>$report_data,
            ];
        }
        // Trend Sales Report
        else{
            return ['gr'=>$gr??[],
                    'dates' => $dates??[],
                    'last_date' =>$last_date,
                    'report_data'=> $report_data??[]];
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
    public function growthRatePerMonth($current_data,$prev_data,$year)
    {

        $final_data = [];
        $prev_year = $year-1;
        foreach ($current_data as $date => $value) {
            $previous_date = date('d-m-',strtotime($date));
            if (date('m',strtotime($date)) ==  02 ) {

                $prev_month = 0;
                if (isset($prev_data['28-02-'.$prev_year])) {
                    $prev_month = $prev_data['28-02-'.$prev_year]??0;
                } elseif (isset($prev_data['29-02-'.$prev_year])){
                    $prev_month = $prev_data['29-02-'.$prev_year]??0;
                }

            } else {
                $prev_month = $prev_data[$previous_date.$prev_year]??0;
            }

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
    public function monthsTotal($total_full_data)
    {
        $result = [];
        foreach ($total_full_data as $date => $value) {
            $month = date('F',strtotime($date));

            $result[$month] =  ($result[$month]??0) + $value ;

        }
        return $result;
    }
}
