<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Http\Controllers\ExportTable;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoicesAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Company $company)
    {
        
        if(Request()->route()->named('invoices.salesChannels.analysis'))
        {
            $type = 'sales_channel';
            $view_name = 'Invoices Against Sales Channel Trend Analysis';
        }

        if(Request()->route()->named('invoices.branches.analysis'))
        {
            $type = 'branch';
            $view_name = 'Invoices Against Branches Trend Analysis';
        }


        if(Request()->route()->named('invoices.zones.analysis'))
        {
            $type = 'zone';
            $view_name = 'Invoices Against Zones Analysis';
        }


		if(Request()->route()->named('invoices.businessSectors.analysis'))
        {
            $type = 'business_sector';
            $view_name = 'Invoices Against Business Sectors Analysis';
        }
		if(Request()->route()->named('invoices.businessUnits.analysis'))
        {
            $type = 'business_unit';
            $view_name = 'Invoices Against Business Units Analysis';
        }
        
         if(Request()->route()->named('invoices.customers.analysis'))
        {
            $type = 'customer_name';
            $view_name = 'Invoices Against Customers Analysis';
        }
        if(Request()->route()->named('invoices.Items.analysis'))
        {
            $type = 'product_item';
            $view_name = 'Invoices Against Products Items Analysis';
        }
        if(Request()->route()->named('invoices.salesPersons.analysis'))
        {
             $type = 'sales_person';
            $view_name = 'Invoices Against Sales Persons Analysis';
        }
      
        
        $name_of_selector_label = str_replace(['Categories Against ' ,' Trend Analysis'],'',$view_name);
        return view('client_view.reports.sales_gathering_analysis.invoices_sales_form', compact('company','name_of_selector_label','type','view_name'));
    }


      public function InvoicesSalesAnalysisResult(Request $request, Company $company , $array = false )
    {
       $report_data =[];
        $growth_rate_data =[];
        $final_report_total =[];
        $branches_names = [];
        $branches = is_array(json_decode(($request->branches[0]))) ? json_decode(($request->branches[0])) :$request->branches ;
        $type = $request->type;
        $view_name = $request->view_name;
        $branches = $this->formatTypesAsString($branches);

        
       $queryResult =  collect(DB::select("select ". $type.",((product_item)) as product_items, ((document_number)) as invoice_number  , Year , Month , net_sales_value
            from sales_gathering
            where document_type in ('INV' , 'inv' , 'invoice','INVOICE','فاتوره') and company_id = ". $company->id ."  
            and ". $type ." in ( ". "\"" . $branches . "\"". ")
            AND date between '".$request->start_date."' and '".$request->end_date."'
            order by year , month"));
            $queryResult = $queryResult->groupBy($type) ;
            $formattedResultForPeriod = $this->formatResultForInterval($queryResult , $request->interval , $type);
		
            $sumForEachInterval  = $this->sumForEachInterval($formattedResultForPeriod);
            $secondTypesArray = $queryResult->pluck($type)->unique()->toArray();
            $reportSalesValues = [];
            $request['sales_channels'] = $request->branches ;
            $request['businessSectors'] = $request->branches ;
            $request['businessUnits'] = $request->branches ;
                $request['zones'] = $request->branches ;
            
            if($type == 'sales_channel')
            {
                 $reportSalesValues  = (new SalesChannelsAgainstAnalysisReport())->SalesChannelsSalesAnalysisResult($request , $company , true);
            }
            if($type == 'branch')
            {
                 $reportSalesValues  = (new BranchesAgainstAnalysisReport())->BranchesSalesAnalysisResult($request , $company , true);
            }
            
            if($type == 'zone')
            {
                 $reportSalesValues  = (new ZoneSalesAnalysisReport())->ZoneSalesAnalysisResult($request , $company , true);
            }

            if($type == 'business_sector')
            {
                 $reportSalesValues  = (new BusinessSectorsAgainstAnalysisReport())->BusinessSectorsSalesAnalysisResult($request , $company , true);
            }
			
			if($type == 'business_unit')
            {
                 $reportSalesValues  = (new BusinessUnitsAgainstAnalysisReport())->BusinessUnitsSalesAnalysisResult($request , $company , true);
            }

           
            if(! $reportSalesValues)
            {
                 $reportSalesValues  =getTypeSalesAnalysisData($request , $company , $type);
            }
          array_sort_multi_levels($sumForEachInterval);
          if($array)
          {
              return [
                  'sumForEachInterval'=>$sumForEachInterval , 
                  'reportSalesValues'=>$reportSalesValues
              ] ;
              
          }

		  return view('client_view.reports.sales_gathering_analysis.invoices_analysis_report',compact('company','view_name','type','secondTypesArray','sumForEachInterval','reportSalesValues'));


    }

    
    public function InvoicesSalesAnalysisIndex(Company $company)
    {
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $selected_fields = (new ExportTable)->customizedTableField($company, 'InventoryStatement', 'selected_fields');
        return view('client_view.reports.sales_gathering_analysis.categories_sales_form', compact('company', 'selected_fields'));
    }
    public function result(Request $request, Company $company,$result='view')
    {
        
    }
    public function resultForSalesDiscount(Request $request, Company $company)
    {

        // $report_data =[];
        // $final_report_data =[];
        // $growth_rate_data =[];
        // $zones_names = [];
        // $sales_values = [];
        // $sales_years = [];
        // $zones = is_array(json_decode(($request->categoriesData[0]))) ? json_decode(($request->categoriesData[0])) :$request->categoriesData ;

        // $type = $request->type;
        // $view_name = $request->view_name;

        // $fields ='';
        // foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
        //     $fields .= $sales_discount_field .',';
        // }


        // foreach ($zones as  $zone) {

        //     $sales =collect(DB::select(DB::raw("
        //         SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , sales_value ," . $fields ." category
        //         FROM sales_gathering
        //         WHERE ( company_id = '".$company->id."'AND category = '".$zone."' AND date between '".$request->start_date."' and '".$request->end_date."')
        //         ORDER BY id"
        //     )))->groupBy('gr_date');
        //     $sales_values_per_zone[$zone] = $sales->map(function($sub_item){
        //                             return $sub_item->sum('sales_value');
        //                         })->toArray();



        //     foreach ($request->sales_discounts_fields as $sales_discount_field_key => $sales_discount_field) {
        //         $zones_discount = $sales->map(function($sub_item) use($sales_discount_field){
        //                                 return $sub_item->sum($sales_discount_field);
        //                             })->toArray();

        //         $zones_sales_values = [];
        //         $zones_per_month = [];
        //         $zones_data = [];
        //         $discount_years = [];

        //         if (@count($zones_discount) > 0) {


        //             array_walk($zones_discount, function ($val, $date) use (&$discount_years) {
        //                 $discount_years[] = date('Y', strtotime($date));
        //             });
        //             $discount_years = array_unique($discount_years);

        //             array_walk($zones_sales_values, function ($val, $date) use (&$sales_years) {
        //                 $sales_years[] = date('Y', strtotime($date));
        //             });
        //             $sales_years = array_unique($sales_years);



        //             $interval_data = Intervals::intervals($sales_values_per_zone, $sales_years, $request->interval);

        //             $sales_values[$zone]  = $interval_data['data_intervals'][$request->interval][$zone] ?? [];




        //             $final_report_data[$zone][$sales_discount_field]['Values'] = $zones_discount;
        //             $interval_data = Intervals::intervals($final_report_data[$zone][$sales_discount_field], $discount_years, $request->interval);
        //             $final_report_data[$zone][$sales_discount_field] = $interval_data['data_intervals'][$request->interval] ?? [];

        //             $final_report_data[$zone]['Total']  = $this->finalTotal([($final_report_data[$zone]['Total']  ?? []) ,($final_report_data[$zone][$sales_discount_field]['Values']??[]) ]);

        //             $final_report_data['Total'] = $this->finalTotal([($final_report_data['Total'] ?? []), (($final_report_data[$zone][$sales_discount_field]['Values']??[]))]);

        //             $final_report_data[$zone][$sales_discount_field]['Perc.% / Sales'] = $this->operationAmongTwoArrays(($final_report_data[$zone][$sales_discount_field]['Values']??[]), ($sales_values[$zone]??[]));


        //         }
        //     }
        //     $zones_names[] = (str_replace( ' ','_', $zone));
        // }

        // $sales_values = $this->finalTotal([$sales_values??[]]);
        // $total = $final_report_data['Total'] ?? [];
        // unset($final_report_data['Total']);
        // $final_report_data['Total'] = $total;
        // $final_report_data['Discount % / Total Sales'] = $this->operationAmongTwoArrays($final_report_data['Total'],$sales_values);

        // $report_data = $final_report_data;

        // $dates = array_keys(($report_data['Total']??[]));

        // $type_name = 'Categories';
        // return view('client_view.reports.sales_gathering_analysis.sales_discounts_analysis_report',compact('company','view_name','zones_names','dates','report_data','type_name'));

    }

    function formatResultForInterval(Collection $queryResult , $interval , $type)
    {
        $startAndEndYear = getYearsFromInterval(Request('start_date') , Request('end_date'));
        $startYear = $startAndEndYear['start_year'];
        $endYear = $startAndEndYear['end_year'];
        $branches  = \array_keys($queryResult->toArray());
        $results = [];
       foreach($branches as $branch)
       {
            for( $startYear ; $startYear <=$endYear ; $startYear++)
            {
			
            foreach(getPeriods($interval) as $periodName => $period)
            {
				
                  foreach($queryResult[$branch] as $result)
                  {
					  $result =  (array)$result;
                      if(in_array($result['Month'] , $period  ) && $result['Year'] == $startYear && $result[$type] == $branch)
                      {
                          isset($results[$branch][$startYear][$periodName][$result['Month']]) ? 
                          array_push($results[$branch][$startYear][$periodName][$result['Month']] , $result) :
                          $results[$branch][$startYear][$periodName][$result['Month']][0] = $result ; 
                      }
                  }
            }
          
        }
        $startYear = $startAndEndYear['start_year'] ;
       }
       return $results;
        
    }

    public function sumForEachInterval(array $array)
    {
        $result = [];
        foreach($array as $branchName=>$dataArray)
        {
            foreach($dataArray as $year => $data)
            {
            foreach($data as $intervalNumber=>$dataToArray)
            {
                $ProductsItemNumber = count_array_values($dataToArray);
                // $ProductsItemNumber = count(array_count_value_for_key($dataToArray  , 'product_items')); 
                $InvoiceNumber = count(array_unique_value($dataToArray  , 'invoice_number')); 
                $result[$branchName][$year][$intervalNumber]['product_item'] =$ProductsItemNumber ;
                $result[$branchName][$year][$intervalNumber]['invoice_number'] = $InvoiceNumber ;
                $result[$branchName][$year][$intervalNumber]['avg'] =$InvoiceNumber ?  $ProductsItemNumber / $InvoiceNumber  : 0 ;
            }
            }
        }
        return $result ;
    } 

    public function formatTypesAsString($branches)
    {
        if(is_array($branches))
        {
            return implode('", "',$branches);
        }
     
        return $branches ;
    }
  
    
	



}
