<?php
    namespace App\Http\Controllers;

use App\Http\Controllers\Analysis\SalesGathering\salesReport;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Traits\GeneralFunctions;

    class SalesForecast
    {
        use GeneralFunctions;
        public function result(Company $company, Request $request)
        {
            $start_date = date('2022-01-01');
            $end_date   = date('2022-12-31');

            if ($request->isMethod('GET')) {
                $request['start_date'] =$start_date;
                $request['end_date'] =$end_date;
            }elseif ($request->isMethod('POST')){
                $start_date = $request['start_date'] ;
                $end_date = $request['end_date'] ;
            }

            $request['report_type'] = 'comparing';
            $start_year = date('Y',strtotime($start_date)) ;
            $date_of_previous_3_years = ($start_year - 3).'-01-01';
            $request['start_date'] = $date_of_previous_3_years;
            $end_date_for_report = ($start_year - 1).'-12-01';
            $request['end_date'] = $start_date;
            $salesReport = (new salesReport)->result($request,$company,'array');
            // Pervious Year Sales
            $previous_1_year_sales =array_sum($salesReport['report_data'][$start_year - 1]['Sales Values'] ?? []);
            $previous_2_years_sales = array_sum($salesReport['report_data'][$start_year - 2]['Sales Values'] ?? []);
            $previous_3_years_sales = array_sum($salesReport['report_data'][$start_year - 3]['Sales Values'] ?? []);
            // Year  Gr Rate
            $previous_year_gr = $previous_2_years_sales ==0 ? 0 : ($previous_1_year_sales - $previous_2_years_sales)/$previous_2_years_sales *100;
            // Average Last 3 Years
            $pervious_years_sales = [
                $start_year - 1 =>$previous_1_year_sales,
                $start_year - 2 =>$previous_2_years_sales,
                $start_year - 3 =>$previous_3_years_sales,
                $start_year - 4 =>0,
            ];
            $pervious_years_sales = array_filter($pervious_years_sales);
            // Average Last 3 Years
            $average_last_3_years = count($pervious_years_sales)  == 0 ? 0 : array_sum($pervious_years_sales)/count($pervious_years_sales);
            // Previous year
            $previous_year = $start_year - 1;
            // Previous Year Seasonality
            $previous_year_seasonality = $salesReport['report_data'][$start_year - 1]['Month Sales %'] ?? [];
            // Last 3 Years Seasonality
            $last_3_years_seasonality = $salesReport['total_full_data']?? [];

            $dates =[] ;
            $quarter_dates = [];
            $counter = 1 ;
            for ($month=0; $month < 12 ; $month++) {
                $date = $this->dateCalc($start_date,$month);
                $dates[] = $date;
                if ($counter % 3 == 0) {
                    $quarter_dates[] = $date;
                }
                $counter++;
            }
            $end_date = date('Y-m-t',strtotime($dates[11]));
            // One Dimention Report
            //
            // Products / Services Sales Breakdown Analysis
            // Products Items Sales Breakdown Analysis
            return view('client_view.forecast.sales_forecast',
                        compact('company',
                                    'start_date',
                                    'end_date',
                                'previous_year',
                                'previous_1_year_sales',
                                'previous_year_gr',
                                'average_last_3_years',
                                'previous_year_seasonality',
                                'last_3_years_seasonality',
                                'quarter_dates',
                                'dates'));
        }

    }

