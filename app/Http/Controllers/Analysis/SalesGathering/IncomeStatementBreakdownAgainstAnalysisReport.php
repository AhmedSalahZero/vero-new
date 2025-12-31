<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\IncomeStatement;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class IncomeStatementBreakdownAgainstAnalysisReport
{
	use GeneralFunctions;
	public function salesBreakdownAnalysisIndex(Company $company)
	{


		if (request()->route()->named('salesBreakdown.zone.analysis')) {
			$type = 'zone';
			$view_name = 'Zones Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.salesChannels.analysis')) {
			$type = 'sales_channel';
			$view_name = 'Sales Channels Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.customers.analysis')) {
			$type = 'customer_name';
			$view_name = 'Customers Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.businessSectors.analysis')) {
			$type = 'business_sector';
			$view_name = 'Business Sectors Sales Breakdown Analysis';
		}elseif (request()->route()->named('salesBreakdown.businessUnits.analysis')) {
			$type = 'business_unit';
			$view_name = 'Business Units Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.branches.analysis')) {
			$type = 'branch';
			$view_name = 'Branches Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.categories.analysis')) {
			$type = 'category';
			$view_name = 'Categories Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.products.analysis')) {
			$type = 'product_or_service';
			$view_name = 'Products / Services Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.Items.analysis')) {
			$type = 'product_item';
			$view_name = 'Products Items Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.salesPersons.analysis')) {
			$type = 'sales_person';
			$view_name = 'Sales Persons Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.salesDiscounts.analysis')) {
			$type = 'sales_discount';
			$view_name = 'Sales Discounts Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.principles.analysis')) {
			$type = 'principle';
			$view_name = 'Principles Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.serviceProvider.analysis')) {
			$type = 'service_provider_name';
			$view_name = 'Service Provider Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.serviceProviderType.analysis')) {
			$type = 'service_provider_type';
			$view_name = 'Service Provider Type Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.serviceProviderAge.analysis')) {
			$type = 'service_provider_birth_year';
			$view_name = 'Service Provider Age Range Sales Breakdown Analysis';
		} elseif (request()->route()->named('salesBreakdown.country.analysis')) {
			$type = 'country';
			$view_name = 'Countries Sales Breakdown Analysis';
		}
		return view('client_view.reports.sales_gathering_analysis.breakdown.sales_form', compact('company', 'view_name', 'type'));
	}

	public function salesBreakdownAnalysisResult(Request $request, Company $company, IncomeStatement $incomeStatement, $subItemType, $isComparingReport = false)
	{
		$report_data = [];
		$report_view_data = [];

		$dates = [
			'start_date' => date('d-M-Y', strtotime($request->start_date)),
			'end_date' => date('d-M-Y', strtotime($request->end_date))
		];
		$report_data = formatReportDataForDashBoard($incomeStatement->duration_type, $incomeStatement->start_from, $incomeStatement->getSubItems(0, $subItemType)->sortBy('id'), $dates['start_date'], $dates['end_date']);
		$report_view_data = $report_data;
		return $report_view_data;
	}


	//  public function salesBreakdownAnalysisResult(Request $request, Company $company,$result='view',$calculated_report_data = null)
	// {
	//     $dimension = $request->report_type;

	//     $report_data =[];
	//     $report_view_data = [];
	//     $growth_rate_data =[];
	//     $report_count_data = [];

	//     $dates = [
	//         'start_date' => date('d-M-Y',strtotime($request->start_date)),
	//         'end_date' => date('d-M-Y',strtotime($request->end_date))
	//     ];

	//     $type = $request->type ;

	//     $view_name = $request->view_name ;
	//           $report_data = isset($calculated_report_data) ? $calculated_report_data :  collect(DB::select(DB::raw("
	//             SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value,service_provider_name," . $type ."
	//             FROM sales_gathering
	//             WHERE ( company_id = '".$company->id."'AND ".$type." IS NOT NULL  AND date between '".$request->start_date."' and '".$request->end_date."')
	//             ORDER BY id "
	//         ))) ;



	//     // $report_data = isset($calculated_report_data) ? $calculated_report_data :  collect(DB::select(DB::raw("
	//     //         SELECT date_formatted as gr_date  , net_sales_value,service_provider_name," . $type ."
	//     //         FROM sales_gathering
	//     //         WHERE ( company_id = '".$company->id."'AND ".$type." IS NOT NULL  AND date between '".$request->start_date."' and '".$request->end_date."')
	//     //         ORDER BY date_formatted "
	//     //     ))) ;
	//     if ($type == 'service_provider_birth_year' || $type == 'service_provider_type') {
	//         $data = $report_data->groupBy($type)->map(function($item,$year)
	//         {
	//             return  $item->groupBy('service_provider_name')->flatMap(function($sub_item,$name)use($item,$year){
	//                     return [
	//                         $name => $sub_item->sum('net_sales_value'),
	//                     ];
	//             });
	//         })->toArray();
	//         if ($type == 'service_provider_birth_year') {


	//             $report_view_data = [
	//                 0=>[
	//                     'item' =>  'Age Range Less Than 40',
	//                     'Sales Value' =>  0,
	//                 ],
	//                 1=>[
	//                     'item' =>  'Age Range 41 - 50',
	//                     'Sales Value' =>  0,
	//                 ],
	//                 2=>[
	//                     'item' =>  'Age Range 51 - 60',
	//                     'Sales Value' =>  0,
	//                 ],
	//                 3=>[
	//                     'item' =>  'Age Range Over 60',
	//                     'Sales Value' =>  0,
	//                 ],
	//             ];
	//             $report_count_data = [
	//                 0=>[
	//                     'item' =>  'Age Range Less Than 40',
	//                     'Count' =>  0,
	//                 ],
	//                 1=>[
	//                     'item' =>  'Age Range 41 - 50',
	//                     'Count' =>  0,
	//                 ],
	//                 2=>[
	//                     'item' =>  'Age Range 51 - 60',
	//                     'Count' =>  0,
	//                 ],
	//                 3=>[
	//                     'item' =>  'Age Range Over 60',
	//                     'Count' =>  0,
	//                 ],
	//             ];
	//             $key = 0;
	//             $current_date = date('Y');
	//             foreach ($data as $year => $data_per_year) {
	//                 $age = $current_date - $year ;
	//                 if ($age <= 40) {
	//                     $key = 0;
	//                 }elseif ($age >= 41 && $age <= 50) {
	//                     $key =  1;
	//                 }elseif ($age >= 51 && $age <= 60) {
	//                     $key =  2;
	//                 }elseif ($age >  60) {
	//                     $key =  3;
	//                 }
	//                 $report_view_data[$key]['Sales Value'] = ($report_view_data[$key]['Sales Value']??0) + array_sum(($data_per_year??[]));
	//                 $report_count_data[$key]['Count'] = ($report_count_data[$key]['Count']??0) + count(($data_per_year??[]));
	//             }

	//         }else {
	//             $key=0;
	//             $report_data = [];
	//             foreach ($data as $type_name => $data_per_year) {
	//                 $report_data[$key]['item'] = $type_name;
	//                 $report_data[$key]['Sales Value'] = ($report_data[$key]['Sales Value']??0) + array_sum(($data_per_year??[]));
	//                 $report_count_data[$key]['item'] = $type_name;
	//                 $report_count_data[$key]['Count'] = ($report_count_data[$key]['Count']??0) + count(($data_per_year??[]));
	//                 $key++;
	//             }

	//         }

	//     }else{

	//         $key_num = 0;
	//         $others =0;
	//         $report_data = $report_data->groupBy($type)->flatMap(function($item,$name){
	//             return   [[ 'item' => $name ,
	//                         'Sales Value' => $item->sum('net_sales_value')]]  ;
	//         })->toArray();
	//     }

	//     if ((count($report_data) > 0 ) && ($type !== 'service_provider_birth_year') && $result !== "withOthers" ) {

	//         $key_num = 0;
	//         $report_data =  collect($report_data)->sortByDesc(function ($data, $key) use($key_num) {
	//             return [($data['Sales Value'])];
	//         });
	//         $viewing_data =$report_data->toArray();
	//         $total_of_all_data = array_sum(array_column($viewing_data,'Sales Value'));

	//         $top_50 = $report_data->take(50);
	//         $others_count = count($report_data) - count($top_50) ;
	//         $report_view_data = $top_50->toArray();
	//         $others_total = $total_of_all_data - array_sum(array_column($report_view_data,'Sales Value'));
	//         if($others_total > 0){
	//             array_push($report_view_data,['item'=>'Others '. $others_count,'Sales Value'=>$others_total]);

	//         }
	//         $key_num = 0;
	//         $final_data = [];

	//         array_walk($report_view_data,function($value,$key) use(&$key_num,&$final_data){
	//             $final_data[$key_num] = $value;
	//             $key_num++;
	//         });
	//         $report_view_data = $final_data;
	//     }
	//     if ($result == 'view') {
	//         if(count($report_data) == 0) {
	//             toastr()->error('No Data Found');
	//             return redirect()->back();
	//         }
	//         $last_date = null;
	//         // Last Date
	//         $last_date = DB::table('sales_gathering')->latest('date')->first()->date;
	//         $last_date = date('d-M-Y',strtotime($last_date));
	//         return view('client_view.reports.sales_gathering_analysis.breakdown.sales_report',compact('last_date','report_count_data','type','view_name','dates','company','report_view_data'));
	//     }elseif ($result == "withOthers") {
	//         return $report_data;
	//     }
	//     else{

	//         if ($type == 'service_provider_birth_year' || $type == 'service_provider_type') {
	//             return   ['report_count_data' =>$report_count_data,
	//                       'report_view_data' =>$report_view_data];
	//         }else {

	//             return $report_view_data;
	//         }
	//     }

	// }

	public function discountsSalesBreakdownAnalysisResult(Request $request, Company $company, $result = 'view')
	{

		$dimension = $request->report_type;

		$report_data = [];
		$report_view_data = [];
		$growth_rate_data = [];
		$last_date = null;
		$dates = [
			'start_date' => date('d-M-Y', strtotime($request->start_date)),
			'end_date' => date('d-M-Y', strtotime($request->end_date))
		];
		$type = $request->type;
		$view_name = $request->view_name;
		$breakdown_items = [
			'special_discount',
			'quantity_discount',
			'other_discounts',
			'cash_discount',
		];
		$report_data = collect(DB::select(DB::raw(
			"
            SELECT  SUM(special_discount) as special_discount , SUM(quantity_discount) as quantity_discount ,SUM(other_discounts) as other_discounts ,SUM(cash_discount) as cash_discount
            FROM sales_gathering
            WHERE ( company_id = '" . $company->id . "' AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
            ORDER BY id "
		)))->flatMap(function ($item) {

			return  [
				0 => [
					"item" => "Special Discount",
					"Sales Value" => $item->special_discount
				],
				1 => [
					"item" => "Quantity Discount",
					"Sales Value" => $item->quantity_discount
				],
				2 => [
					"item" => "Other Discounts",
					"Sales Value" => $item->other_discounts
				],
				3 => [
					"item" => "Cash Discount",
					"Sales Value" => $item->cash_discount
				]
			];
		});


		$report_view_data =  collect($report_data)->sortByDesc(function ($data, $key) {

			return [$data['Sales Value']];
		})->values()->toArray();






		if ($result == 'view') {
			if (count($breakdown_items) == 0) {
				toastr()->error('No Data Found');
				return redirect()->back();
			}
			$last_date = null;
			// Last Date
			$last_date = SalesGathering::company()->latest('date')->first()->date;
			$last_date = date('d-M-Y', strtotime($last_date));
			return view('client_view.reports.sales_gathering_analysis.breakdown.sales_report', compact('last_date', 'type', 'view_name', 'dates', 'company', 'report_view_data'));
		} else {
			return $report_view_data;
		}
	}


	public function getNetSalesValueSum(Request $request)
	{

		$companyId = $request->get('company_id');
		$selectedType = $request->get('selectedType');
		$start_date = $request->get('start_date');
		$end_date = $request->get('end_date');
		$type = $request->get('type');
		$modal_id = $request->get('modal_id');
		$db = DB::select(DB::raw(
			'
             SELECT "' . $selectedType . '" as selected_type_name , "' . $modal_id . '" as modal_id , FORMAT(sum(net_sales_value) , 0) as total_sales_value , count(DISTINCT(customer_name)) as customer_name , count(DISTINCT(category)) as category , count(DISTINCT(product_or_service)) as product_or_service , count(DISTINCT(product_item)) as product_item, count(DISTINCT(sales_person)) as sales_person ,
              count(DISTINCT(business_sector)) as business_sector, count(DISTINCT(sales_channel)) as sales_channel, count(DISTINCT(zone)) as zone, count(DISTINCT(branch)) as branch
                FROM sales_gathering
                force index (sales_channel_index)
                WHERE ( company_id = ' . $companyId  . ' AND ' . $type .  ' =  "'  . $selectedType .  '" AND date between "' . $start_date . '" and "' . $end_date . '"  )
                ORDER BY id '
		));

		$request['branches'] = [$selectedType];
		$request['type'] = $type;
		$request['interval'] = 'annually';


		$invoiceResultArray = (new InvoicesAgainstAnalysisReport())->InvoicesSalesAnalysisResult($request, Company::find($companyId), true);
		$invoiceResultsFormatted = formatInvoiceForEachInterval($invoiceResultArray, $selectedType);
		foreach ($invoiceResultsFormatted as $key => $value) {
			$db[0]->{$key} = $value;
		}
		return response()->json([
			'data' => $db
		]);
	}


	public function topAndBottomsForDashboard(Request $request)
	{
		$companyId = $request->get('company_id');
		$company = Company::find($companyId);
		$type = $request->get('type');
		$direction = $request->get('direction');
		$column = $request->get('column');
		$start_date = $request->get('date_from');
		$end_date = $request->get('date_to');
		$modal_id = $request->get('modal_id');
		$selectedType = $request->get('selected_type');
		$request['date'] = $end_date;


		$queryResult = DB::select(DB::raw(
			'
             SELECT "' . $selectedType . '" as selected_type_name , "' . $modal_id . '" as modal_id , sum(net_sales_value)  as total_sales_value ,  ' . $column . ' as customer_name
                FROM sales_gathering
                force index (sales_channel_index)
                WHERE ( company_id = ' . $companyId  . ' AND ' . $type .  ' =  "'  . $selectedType .  '" AND date between "' . $start_date . '" and "' . $end_date . '"  )
                 group by ' . $column . '
                 ORDER BY total_sales_value ' . ($direction == 'top' ? 'DESC limit 50' : 'ASC limit 50')
		));

		return response()->json([
			'data' => $queryResult,
			'modal_id' => $modal_id
		]);
	}
}
