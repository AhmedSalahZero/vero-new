<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
class IntervalsComparingReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {

        if (request()->route()->named('intervalComparing.zone.analysis')) {
            $type = 'zone';
            $view_name = 'Zones Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.salesChannels.analysis')) {
            $type = 'sales_channel';
            $view_name = 'Sales Channels Sales Interval Comparing Analysis' ;
        } elseif (request()->route()->named('intervalComparing.customers.analysis')) {
            $type = 'customer_name';
            $view_name = 'Customers Sales Interval Comparing Analysis' ;

        } elseif (request()->route()->named('intervalComparing.businessSectors.analysis')) {
            $type = 'business_sector';
            $view_name = 'Business Sectors Sales Interval Comparing Analysis' ;

        }elseif (request()->route()->named('intervalComparing.businessUnits.analysis')) {
            $type = 'business_unit';
            $view_name = 'Business Units Sales Interval Comparing Analysis' ;

        } elseif (request()->route()->named('intervalComparing.branches.analysis')) {
            $type = 'branch';
            $view_name = 'Branches Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.categories.analysis')) {
            $type = 'category';
            $view_name = 'Categories Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.products.analysis')) {
            $type = 'product_or_service';
            $view_name = 'Products / Services Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.Items.analysis')) {
            $type = 'product_item';
            $view_name = 'Products Items Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.salesPersons.analysis')) {
            $type = 'sales_person';
            $view_name = 'Sales Persons Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.salesDiscounts.analysis')) {
            $type = 'sales_discount';
            $view_name = 'Sales Discounts Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.principles.analysis')) {
            $type = 'principle';
            $view_name = 'Principles Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.serviceProviders.analysis')) {
            $type = 'service_provider_name';
            $view_name = 'Service Provider Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.serviceProvidersType.analysis')) {
            $type = 'service_provider_type';
            $view_name = 'Service Provider Type Sales Interval Comparing Analysis' ;
        }
        elseif (request()->route()->named('intervalComparing.serviceProvidersBirthYear.analysis')) {
            $type = 'service_provider_birth_year';
            $view_name = 'Service Provider Age Range Sales Interval Comparing Analysis' ;
        }elseif (request()->route()->named('intervalComparing.country.analysis')) {
            $type = 'country';
            $view_name = 'Countries Sales Interval Comparing Analysis' ;
        }
        return view('client_view.reports.sales_gathering_analysis.interval_comparing.sales_form', compact('company', 'view_name','type'));
    }

    public function result(Request $request, Company $company,$result='view')
    {
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
        $result_for_interval_one = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request,$company,'array');

        if (isset($result_for_interval_one['report_count_data'])) {
            $count_result_for_interval_one = $result_for_interval_one['report_count_data'];
            $result_for_interval_one = $result_for_interval_one['report_view_data'];
        }
        // Second_interval
        $request['start_date']=$start_date_two;
        $request['end_date']=$end_date_two;

        $result_for_interval_two = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request,$company,'array');
        if (isset($result_for_interval_two['report_count_data'])) {
			$count_result_for_interval_two = $result_for_interval_two['report_count_data'];
            $result_for_interval_two = $result_for_interval_two['report_view_data'];
        }
		
// third interval

        //   $request['start_date']=$start_date_three;
        // $request['end_date']=$end_date_three;

        // $result_for_interval_three = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request,$company,'array');
        // if (isset($result_for_interval_three['report_count_data'])) {
        //     $count_result_for_interval_three = $result_for_interval_three['report_count_data'];
        //     $result_for_interval_three = $result_for_interval_three['report_view_data'];
        // }

        // Last Date
        $last_date = null;
        $last_date = SalesGathering::company()->latest('date')->first() ? SalesGathering::company()->latest('date')->first()->date : null;
        $last_date = $last_date ? date('d-M-Y',strtotime($last_date)) : null ;

        if ($result == 'view') {
            // if(count($breakdown_items) == 0) {
            //     toastr()->error('No Data Found');
            //     return redirect()->back();
            // }
            $last_date = null;
            // Last Date
            $last_date = SalesGathering::company()->latest('date')->first()->date;
            $last_date = date('d-M-Y',strtotime($last_date));
            
            return view('client_view.reports.sales_gathering_analysis.interval_comparing.sales_report',compact('last_date','type','view_name','dates','company','result_for_interval_one','result_for_interval_two',
            // 'result_for_interval_three',
            'count_result_for_interval_one','count_result_for_interval_two','latestReport'
            // ,'count_result_for_interval_three'
            ));
        }else{
            
            return ['result_for_interval_one' => $result_for_interval_one,
                    'result_for_interval_two' => $result_for_interval_two,
                    // 'result_for_interval_three' => $result_for_interval_three,
                ];
        }

    }

    public function discountsComparingResult(Request $request, Company $company,$result = 'view')
    {
        $start_date_one  = $request->start_date_one;
        $end_date_one  = $request->end_date_one;
        $start_date_two  = $request->start_date_two;
        $end_date_two  = $request->end_date_two;
        $type = $request->type ;
        $view_name = $request->view_name ;
        
        $dates = [
            'start_date_one' => date('d-M-Y',strtotime($start_date_one)),
            'end_date_one' => date('d-M-Y',strtotime($end_date_one)),
            'start_date_two' => date('d-M-Y',strtotime($start_date_two)),
            'end_date_two' => date('d-M-Y',strtotime($end_date_two))
        ];

        $request['start_date']=$start_date_one;
        $request['end_date']=$end_date_one;

        $result_for_interval_one = (new SalesBreakdownAgainstAnalysisReport)->discountsSalesBreakdownAnalysisResult($request,$company,'array');
        $request['start_date']=$start_date_two;
        $request['end_date']=$end_date_two;

        $result_for_interval_two = (new SalesBreakdownAgainstAnalysisReport)->discountsSalesBreakdownAnalysisResult($request,$company,'array');


        // Last Date
        $last_date = null;
        $last_date = SalesGathering::company()->latest('date')->first()->date;
        $last_date = date('d-M-Y',strtotime($last_date));

        if ($result == 'view') {
            // if(count($breakdown_items) == 0) {
            //     toastr()->error('No Data Found');
            //     return redirect()->back();
            // }
            $last_date = null;
            // Last Date
            $last_date = SalesGathering::company()->latest('date')->first()->date;
            $last_date = date('d-M-Y',strtotime($last_date));
            return view('client_view.reports.sales_gathering_analysis.interval_comparing.sales_report',compact('last_date','type','view_name','dates','company','result_for_interval_one','result_for_interval_two'));
        }else{
            return ['result_for_interval_one' => $result_for_interval_one,
                    'result_for_interval_two' => $result_for_interval_two
                ];
        }

    }


}
