<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ZoneSalesAnalysisReport
{
    use GeneralFunctions;
    public function ZoneSalesAnalysisIndex(Company $company)
    {
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        // $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
        $zones =  SalesGathering::company()->whereNotNull('zone')->where('zone','!=','')->groupBy('zone')->selectRaw('zone')->get()->pluck('zone')->toArray();
        return view('client_view.reports.sales_gathering_analysis.zone_sales_form', compact('company','zones'));
    }
    public function ZoneSalesAnalysisResult(Request $request, Company $company , $array = false )
    {
        $dimension = $request->report_type;

        $report_data =[];
        $growth_rate_data =[];
        $zones = is_array(json_decode(($request->zones[0]))) ? json_decode(($request->zones[0])) :$request->zones ;

        foreach ($zones as  $zone) {

            $sales_gatherings = SalesGathering::company()
                    ->where('zone',$zone)
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->selectRaw('DATE_FORMAT(date,"%d-%m-%Y") as date,net_sales_value,zone')
                    ->get()
                    ->toArray();

            $zones_per_month = [];
            $zones_data = [];


            $dt = Carbon::parse($sales_gatherings[0]['date']);
            $month = $dt->endOfMonth()->format('d-m-Y');



            foreach ($sales_gatherings as $key => $row) {

                $dt = Carbon::parse($row['date']);
                $current_month = $dt->endOfMonth()->format('d-m-Y');
                if($current_month == $month){
                    $zones_per_month[$current_month][] = $row['net_sales_value'];
                }else{
                    $month = $current_month;
                    $zones_per_month[$current_month][] = $row['net_sales_value'];
                }

                $zones_data[$month] = array_sum($zones_per_month[$month]);
            }

            $report_data[$zone] = $zones_data;
            $growth_rate_data[$zone] = $this->growthRate($zones_data);

        }

        $total_zones = $this->finalTotal($report_data);
        $total_zones_growth_rates =  $this->growthRate($total_zones);
        $final_report_data = [];
        $zones_names =[];
        foreach ($zones as  $zone) {
            $final_report_data[$zone]['Sales Values'] = $report_data[$zone];
            $final_report_data[$zone]['Growth Rate %'] = $growth_rate_data[$zone];
            $zones_names[] = (str_replace( ' ','_', $zone));
        }

        if($array)
        {
            return $report_data ;
        }

		$final_report_data = HArr::getKeysSortedDescByKey($final_report_data,'Sales Values');
        return view('client_view.reports.sales_gathering_analysis.zone_sales_report',compact('company','zones_names','total_zones_growth_rates','final_report_data','total_zones'));

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
