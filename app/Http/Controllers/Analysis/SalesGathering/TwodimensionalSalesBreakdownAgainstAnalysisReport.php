<?php
namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Helpers\HArr;
use App\Models\Company;
use App\Models\SalesGathering;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TwodimensionalSalesBreakdownAgainstAnalysisReport
{
    use GeneralFunctions;
    public function index(Request $request, Company $company)
    {
        if (request()->route()->named('zone.vs.salesChannels.view')) {
            $main_type = 'zone';
            $type = 'sales_channel';
            $view_name = 'Zones Versus Sales Channels' ;
        }elseif (request()->route()->named('salesChannels.vs.zones.view')) {
            $main_type = 'sales_channel';
            $type = 'zone';
            $view_name = 'Sales Channels Versus Zones' ;
        }elseif (request()->route()->named('products.vs.zones.view')) {
            $main_type = 'product_or_service';
            $type = 'zone';
            $view_name = 'Products Versus Zones' ;
        }elseif (request()->route()->named('products.vs.salesChannels.view')) {
            $main_type = 'product_or_service';
            $type = 'sales_channel';
            $view_name = 'Products Versus Sales Channels' ;
        }elseif (request()->route()->named('Items.vs.salesChannels.view')) {
            $main_type = 'product_item';
            $type = 'sales_channel';
            $view_name = 'Products Items Versus Sales Channels' ;
        }elseif (request()->route()->named('Items.vs.zones.view')) {
            $main_type = 'product_item';
            $type = 'zone';
            $view_name = 'Products Items Versus Zones' ;

        }elseif (request()->route()->named('categories.vs.salesChannels.view')) {
            $main_type = 'category';
            $type = 'sales_channel';
            $view_name = 'Category Versus Sales Channels' ;
        }elseif (request()->route()->named('categories.vs.zones.view')) {
            $main_type = 'category';
            $type = 'zone';
            $view_name = 'Category Versus Zones' ;
        }elseif (request()->route()->named('Items.vs.businessSectors.view')) {
            $main_type = 'product_item';
            $type = 'business_sector';
            $view_name = 'Products Items Versus Business Sectors' ;
        }elseif (request()->route()->named('products.vs.businessSectors.view')) {
            $main_type = 'product_or_service';
            $type = 'business_sector';
            $view_name = 'Products Versus Business Sectors' ;
        }
        
        elseif (request()->route()->named('categories.vs.businessSectors.view')) {
            $main_type = 'category';
            $type = 'business_sector';
            $view_name = 'Category Versus Business Sectors' ;
        }
		
		
		//
		
		elseif (request()->route()->named('Items.vs.businessUnits.view')) {
            $main_type = 'product_item';
            $type = 'business_unit';
            $view_name = 'Products Items Versus Business Units' ;
        }elseif (request()->route()->named('products.vs.businessUnits.view')) {
            $main_type = 'product_or_service';
            $type = 'business_unit';
            $view_name = 'Products Versus Business Units' ;
        }
        
        elseif (request()->route()->named('categories.vs.businessUnits.view')) {
            $main_type = 'category';
            $type = 'business_unit';
            $view_name = 'Category Versus Business Units' ;
        }
		
		//
		
         elseif (request()->route()->named('categories.vs.branches.view')) {
            $main_type = 'category';
            $type = 'branch';
            $view_name = 'Category Versus Branches' ;
        }

          elseif (request()->route()->named('Items.vs.branches.view')) {
            $main_type = 'product_item';
            $type = 'branch';
            $view_name = 'Products Items  Versus Branches' ;
        }

         elseif (request()->route()->named('products.vs.branches.view')) {
            $main_type = 'product_or_service';
            $type = 'branch';
            $view_name = 'Products Versus Branches' ;
        }

        
        elseif (request()->route()->named('customers.vs.businessSectors.view')) {
            $main_type = 'customer_name';
            $type = 'business_sector';
            $view_name = 'Customers Versus Business Sectors' ;
        }
        
        elseif (request()->route()->named('customers.vs.salesChannels.view')) {
            $main_type = 'customer_name';
            $type = 'sales_channel';
            $view_name = 'Customers Versus Sales Channels' ;
        }elseif (request()->route()->named('customers.vs.zones.view')) {
            $main_type = 'customer_name';
            $type = 'zone';
            $view_name = 'Customers Versus Zones' ;
        }elseif (request()->route()->named('branches.vs.salesChannels.view')) {
            $main_type = 'branch';
            $type = 'sales_channel';
            $view_name = 'Branches Versus Sales Channels' ;
        }elseif (request()->route()->named('zone.vs.discounts.view')) {
            $main_type = 'zone';
            $type = 'discounts';
            $view_name = 'Zones Versus Discounts' ;
        }elseif (request()->route()->named('businessSectors.vs.salesChannels.view')) {
            $main_type = 'business_sector';
            $type = 'sales_channel';
            $view_name = 'Business Sectors Versus Sales Channels' ;
        }elseif (request()->route()->named('businessUnits.vs.salesChannels.view')) {
            $main_type = 'business_unit';
            $type = 'sales_channel';
            $view_name = 'Business Units Versus Sales Channels' ;
        }elseif (request()->route()->named('businessUnits.vs.salesChannels.view')) {
            $main_type = 'business_unit';
            $type = 'sales_channel';
            $view_name = 'Business Units Versus Sales Channels' ;
        }elseif (request()->route()->named('customers.vs.salesChannels.view')) {
            $main_type = 'customer_name';
            $type = 'sales_channel';
            $view_name = 'Customers Versus Sales Channels' ;
        }elseif (request()->route()->named('countries.vs.salesChannels.view')) {
            $main_type = 'country';
            $type = 'sales_channel';
            $view_name = 'Countries Versus Sales Channels' ;
        }elseif (request()->route()->named('countries.vs.businessSectors.view')) {
            $main_type = 'country';
            $type = 'business_sector';
            $view_name = 'Countries Versus Business Sectors' ;
        }elseif (request()->route()->named('countries.vs.businessUnits.view')) {
            $main_type = 'country';
            $type = 'business_unit';
            $view_name = 'Countries Versus Business Units' ;
        }elseif (request()->route()->named('countries.vs.Items.view')) {
            $main_type = 'country';
            $type = 'product_item';
            $view_name = 'Countries Versus Products Items' ;
        }
		elseif (request()->route()->named('branches.vs.day.view')) {
            $main_type = 'branch';
            $type = 'day_name';
            $view_name = 'Branches Versus Day Name Sales' ;
        }
		elseif (request()->route()->named('salesChannels.vs.day.view')) {
            $main_type = 'sales_channel';
            $type = 'day_name';
            $view_name = 'Sales Channels Versus Day Name Sales' ;
        }
		elseif (request()->route()->named('categories.vs.day.view')) {
            $main_type = 'category';
            $type = 'day_name';
            $view_name = 'Categories Versus Day Name Sales' ;
        }
		elseif (request()->route()->named('Items.vs.day.view')) {
            $main_type = 'product_item';
            $type = 'day_name';
            $view_name = 'Product Items Versus Day Name Sales' ;
        }
		elseif (request()->route()->named('businessUnits.vs.day.view')) {
            $main_type = 'business_unit';
            $type = 'day_name';
            $view_name = 'Business Units Versus Day Name Sales' ;
        }
		elseif (request()->route()->named('products.vs.day.view')) {
            $main_type = 'product_or_service';
            $type = 'day_name';
            $view_name = 'Product / Services Versus Day Name Sales' ;
        }
        return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_form', compact('company', 'view_name','type','main_type'));
    }
	public function getBundlingData(Request $request  ,Company $company,$main_type)
	{
		$allNames = [];
			$report_data =collect(DB::select(DB::raw("
            SELECT  document_number ,sum(quantity) as group_quantity,sum(net_sales_value) as group_net_sales_value , ".$main_type."
            FROM sales_gathering 
			where  company_id = '".$company->id."'   AND date between '".$request->start_date."' and '".$request->end_date."'
			group by ".$main_type." , document_number
			
			"
            )))
			->groupBy(['document_number'])
			->toArray();
			$quantity = 0 ;
			$items = [];
			$top50 = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request,$company,'array',null,null,20) ;
			unset($top50[50]);
			$top50 = array_column($top50,'item');

			

			foreach($top50 as $searchItemName){
			
				foreach($report_data as $documentNumber=>$subData){
					if(in_array($searchItemName,array_column($subData,$main_type))){
						$item = collect($subData)->where($main_type,$searchItemName)->first();
						$itemName = $item->{$main_type} ;
						$allNames[$itemName] = $itemName;
						$items[] = [
							'document_number'=>$item->document_number,
							$main_type=>$itemName,
							'quantity'=>$item->group_quantity ,
							'net_sales_value'=>$item->group_net_sales_value ,
						];
					}
				}
			}
			$finalResult = [];
				foreach($items as $item){
					$mainName = $item[$main_type] ;
					foreach($items as $item2){
						$subName = $item2[$main_type] ;
						if( $subName != $mainName 
						&& $item2['document_number'] == $item['document_number']  
						){
							// $finalResult[$mainName][$subName]['quantity'] = isset($finalResult[$mainName][$subName]['quantity']) ? $finalResult[$mainName][$subName]['quantity'] + $item['quantity'] : $item['quantity'];
							// $finalResult[$mainName][$subName]['net_sales_value'] = isset($finalResult[$mainName][$subName]['net_sales_value']) ? $finalResult[$mainName][$subName]['net_sales_value'] + $item['net_sales_value'] : $item['net_sales_value'];
							$finalResult[$mainName][$subName] = isset($finalResult[$mainName][$subName]) ? $finalResult[$mainName][$subName] + $item['net_sales_value'] : $item['net_sales_value'];
						}
					}
					
				}
				return $finalResult;
	}
    public function result(Request $request, Company $company)
    {
        $report_data =[];
        $main_type = $request->main_type;
        $type = $request->type;
        $view_name = $request->view_name;
		$last_date = SalesGathering::company()->latest('date')->first()->date ?? null;
        $last_date = date('d-M-Y',strtotime($last_date));
        $dates = [
            'start_date' => date('d-M-Y',strtotime($request->start_date)),
            'end_date' => date('d-M-Y',strtotime($request->end_date))
        ];
		if($main_type == $type){
			$report_data = $this->getBundlingData($request,$company,$main_type);
			$all_items =array_keys($report_data); 
			$main_type_items = $all_items;
			$items_totals = $this->finalTotal([$report_data]);
			$main_type_items_totals = [];
			foreach ($report_data as  $main_type_item_name => $sales_gathering_data) {
				$main_type_items_totals[$main_type_item_name] = array_sum($report_data[$main_type_item_name]??[]);
			}
			return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.bundling_report',compact('company','view_name', 'main_type','type', 'all_items','main_type_items','report_data','last_date','dates','items_totals','main_type_items_totals'));
					
		}
       
        $all_items = [];

        $main_type_items_totals = [];
		
		//////////////////////////////////////////
		
		
		

			$report_data =collect(DB::select(DB::raw("
            SELECT DATE_FORMAT(date,'%d-%m-%Y') as date, net_sales_value ,sales_value,document_number,".$type.",".$main_type ."
            FROM sales_gathering
            WHERE ( company_id = '".$company->id."' AND ".$type." IS NOT NULL AND ".$main_type." IS NOT NULL  AND date between '".$request->start_date."' and '".$request->end_date."')
             ORDER BY id "
            )))->groupBy($main_type)->map(function($item) use($type){
                return $item->groupBy($type)->map(function($sub_item){
                    return $sub_item->sum('net_sales_value');
                });
            })->toArray();

        $main_type_items = array_keys(($report_data??[]));
	
        foreach ($report_data as  $main_type_item_name => $sales_gathering_data) {
            $main_type_items_totals[$main_type_item_name] = array_sum($report_data[$main_type_item_name]??[]);
        }
        arsort($main_type_items_totals);
	
        $items_totals = $this->finalTotal([$report_data]);
		$items_totals = $type =='day_name' ? HArr::orderByDayNameForOneDimension($items_totals) : $items_totals;
        $all_items =   array_keys($items_totals);


        if(count($main_type_items_totals) > 50){
            $report_view_data = collect($main_type_items_totals);
            $top_20 = $report_view_data->take(50);
            $report_view_data = $top_20->toArray();
            $main_type_items_totals = $report_view_data;
            foreach ($report_view_data as $name_of_main_item => $data) {
                $result[$name_of_main_item] =$report_data[$name_of_main_item];
                unset($report_data[$name_of_main_item]);
            }
            $result['Others '.count($report_data)] =  $this->finalTotal([$report_data]);

            $main_type_items_totals['Others '.count($report_data)]  = array_sum(($result['Others '.count($report_data)]??[]));
            $report_data = $result;
        }
        if($request->get('direction') == 'asc')
        {
            $report_data = \array_reverse($report_data , true );
        $main_type_items_totals = \array_reverse($main_type_items_totals , true );
        
        }
      
        $all_items = array_unique($all_items);
        return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_report',compact('company','view_name', 'main_type','type', 'all_items','main_type_items','report_data','last_date','dates','items_totals','main_type_items_totals'));

    }


    public function discountsResult(Request $request, Company $company)
    {
        {

            $report_data =[];
            $main_type = $request->main_type;
            $type = 'discounts';
            $view_name = $request->view_name;
            $last_date = null;
            $dates = [
                'start_date' => date('d-M-Y',strtotime($request->start_date)),
                'end_date' => date('d-M-Y',strtotime($request->end_date))
            ];
            $all_items = [];
            $main_type_items = SalesGathering::company()->whereNotNull($main_type)->groupBy($main_type)->selectRaw($main_type)->whereBetween('date', [$request->start_date, $request->end_date])->get()->pluck($main_type)->toArray();
            // $items = SalesGathering::company()->whereNotNull($main_type)->groupBy($main_type)->selectRaw($main_type)->whereBetween('date', [$request->start_date, $request->end_date])->get()->pluck($main_type)->toArray();
            $all_items = [
                'quantity_discount' => 'Quantity Discount' ,
                'cash_discount' => 'Cash Discount' ,
                'special_discount' => 'Special Discount' ,
                'other_discounts' => 'Other Discount' ,
            ];
            $totals_sales_per_main_type = [];
            $total_sales = 0;
            foreach ($main_type_items as  $main_type_item_name) {


                    foreach ($all_items as  $field => $field_name) {
                        $sales_gatherings = SalesGathering::company()
                                    ->where($main_type,$main_type_item_name)
                                    ->whereBetween('date', [$request->start_date, $request->end_date])
                                    ->selectRaw('DATE_FORMAT(date,"%d-%m-%Y") as date,sales_value,'.$field.','.$main_type)
                                    ->get();

                        $field_total = collect($sales_gatherings)->sum('sales_value');
                        $total_sales += $field_total ;
                        $totals_sales_per_main_type[$main_type_item_name] =  $field_total;
                        $main_type_items_per_month = [];
                        $main_type_items_data = [];

                        $total = collect($sales_gatherings)->sum($field);

                        $report_data[$main_type_item_name][$field_name] =$total;


                    }


                $main_type_items_totals[$main_type_item_name] = array_sum($report_data[$main_type_item_name]??[]);
            }

            $items_totals = $this->finalTotal([$report_data]);
            arsort($main_type_items_totals);


            if(count($main_type_items_totals) > 50){
                $report_view_data = collect($main_type_items_totals);
                $top_20 = $report_view_data->take(50);
                $report_view_data = $top_20->toArray();
                $main_type_items_totals = $report_view_data;
                foreach ($report_view_data as $name_of_main_item => $data) {
                    $result[$name_of_main_item] =$report_data[$name_of_main_item];
                    unset($report_data[$name_of_main_item]);
                }
                $result['Others '.count($report_data)] =  $this->finalTotal([$report_data]);

                $main_type_items_totals['Others '.count($report_data)]  = array_sum(($result['Others '.count($report_data)]??[]));
                $report_data = $result;
            }

            $last_date = SalesGathering::company()->latest('date')->first()->date;
            $last_date = date('d-M-Y',strtotime($last_date));
            $all_items = array_unique($all_items);

            return view('client_view.reports.sales_gathering_analysis.two_dimensional_breakdown.sales_report',compact('company','view_name', 'main_type','type','all_items','main_type_items','report_data','last_date','dates','items_totals','main_type_items_totals','totals_sales_per_main_type','items_totals'));

        }
    }
}
