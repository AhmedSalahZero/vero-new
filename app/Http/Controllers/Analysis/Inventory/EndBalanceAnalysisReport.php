<?php
    namespace App\Http\Controllers\Analysis\Inventory;

use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\InventoryStatement;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EndBalanceAnalysisReport
    {
        use GeneralFunctions;
        public function index(Company $company)
        {
            // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
            $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
            return view('client_view.reports.inventory_analysis.end_balance_form',compact('company','selected_fields'));
        }
        public function result(Request $request, Company $company,$result='view')
        {

            $inventory = InventoryStatement::company()->where(function ($query) use($request) {
                if(isset($request->category) && $request->category !== null){
                    $query->where('category',$request->category);
                }
                if(isset($request->sub_category) && $request->sub_category !== null){
                    $query->where('sub_category',$request->sub_category);
                }
                if(isset($request->product) && $request->product !== null){
                    $query->where('product',$request->product);
                }
                if(isset($request->product_item) && $request->product_item !== null){
                    $query->where('product_item',$request->product_item);
                }
            })->selectRaw('DATE_FORMAT(date,"%d-%m-%Y") as date, beginning_balance ,volume_out')->get();

            $last_date = $inventory->last()->date;

            $first_date = $this->dateCalc($last_date,-$request->duration);



            $end_balances = $inventory->pluck('beginning_balance','date')->toArray();
            $rows = $inventory->toArray();



            $new_end_balances=[] ;
            $new_end_balances_with_last_months=[] ;

            foreach ($end_balances as $date => $end_balance) {
                $current_date = date('01-m-Y',strtotime($date));
                $dt = Carbon::parse($date);
                $final_date = $dt->endOfMonth()->format('d-m-Y');
                $new_date = $this->dateCalc($date,0,'01-m-Y');

                $new_end_balances_with_last_months[$final_date] = $end_balance;
                $new_end_balances[$new_date] = $end_balance;


            }

            $volume_outs_data=[] ;
            $total_month = 0;
            $first_date = $inventory->first()->date;
            $current_month = Carbon::parse($first_date)->format('01-m-Y');
            $last_month = Carbon::parse($last_date)->format('01-m-Y');

            $first_month = $current_month ;

            // foreach ($rows as $key => $row) {
            //     $date = $row['date'];

            //     $dt = Carbon::parse($date);
            //     $month = $dt->format('01-m-Y');

            //     $end_balance = $row['end_balance'];
            //     $volume_out = $row['volume_out'];

            //     if ($current_month == $month) {
            //         $total_month += $volume_out;
            //         if($first_month == $current_month){
            //             $volume_outs_data[$date] = $total_month;
            //         }
            //     }elseif($current_month != $month && $last_month != $month) {
            //         $volume_outs_data[$date] = $total_month;
            //         $current_month = $month;
            //         $total_month =0;
            //         $total_month += $volume_out;
            //     }elseif ($last_month == $month) {
            //         $total_month += $volume_out;
            //         $volume_outs_data[$last_date]=$total_month;
            //     }
            //     // }elseif ($last_date == $date) {
            //     //     $total_month += $volume_out;
            //     //     $volume_outs_data[$last_date]=$total_month;
            //     // }
            //     // $current_date = $date;

            // }


            $final = ['End Balance'=>$new_end_balances];
            $indexes_intervals_type = [
                "End Balance"   => "end_balance_interval",
            ];
            $date_collection =  ['start_date'=>$first_date,'duration'=>$request->duration]  ;

            $data =  intervals::intervals($final, $date_collection, 'multi', $indexes_intervals_type, false, false, false);

            $end_balance = $data['data_intervals'][$request->interval??'quarterly']['End Balance'];
            $end_balance_final =[];
            array_walk($end_balance, function ($val,$date) use(&$end_balance_final)
            {
                $dt = Carbon::parse($date);
                $final_date = $dt->endOfMonth()->format('d-m-Y');
                $end_balance_final[$final_date] = $val;
            });
            $final = ['End Balance'=>$end_balance_final];

            // Turn Over

            $dates_annually = $data['years_intervals_months']['annually'];
            $this->intervalsCalculation($volume_outs,$dates_annually) ;


        }

        public function intervalsCalculation($income_statement,$years)
        {
            $year = date('Y',strtotime(($years[0])));

            $income_statement_updated_data = [];

                $total_per_year = 0;
                foreach ($income_statement as $date => $value) {
                    $current_year = date('Y',strtotime($date));
                    if($current_year == $year){
                        $total_per_year +=$value;
                        $income_statement_updated_data[$date] = $total_per_year;
                    }else  {
                        $total_per_year = 0;
                        $total_per_year +=$value;
                        $income_statement_updated_data[$date] = $total_per_year;
                        $year = $current_year;
                    }
                }

            return$income_statement_updated_data ;
        }
    }
