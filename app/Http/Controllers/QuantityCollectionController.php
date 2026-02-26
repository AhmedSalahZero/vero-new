<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\QuantityAllocationSetting;
use App\Models\QuantityCollectionSetting;
use App\Models\QuantityExistingProductAllocationBase;
use App\Models\QuantityNewProductAllocationBase;
use App\Models\QuantitySalesForecast;
use App\Models\QuantitySecondAllocationSetting;
use App\Models\QuantitySecondExistingProductAllocationBase;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;
class QuantityCollectionController extends Controller
{
    use GeneralFunctions;
    public function collectionSettings(Request $request, Company $company)
    {
		$first_allocation_setting_base = QuantityAllocationSetting::company()->first()->allocation_base ?? null;
        $second_allocation_setting_base = QuantitySecondAllocationSetting::company()->first()->allocation_base ?? null;
        // Saving Data
        if ($request->isMethod('POST')) {
			$total = [];
            $validation['collection_base'] = 'required';
			
            if ($request->collection_base == 'general_collection_policy') {
				
				$total_percentage = array_sum(array_column($request->general_collection,'rate'));
                $validation['total_rate.general_collection_policy'] = $total_percentage != 100 ? 'required' : '' ;
            } elseif($request->collection_base == $first_allocation_setting_base) {
				
				
				foreach ($request->first_allocation_collection as $base => $values) {
					$total[$base] = array_sum(array_column($values,'rate'));
                    $validation['total_rate.'.$base] =  ($total[$base] != 100 ) ? 'required' :  ''  ;
                }
            }elseif($request->collection_base == $second_allocation_setting_base) {
				
				foreach ($request->second_allocation_collection as $base => $values) {
                    $total[$base] = array_sum(array_column($values,'rate'));
                    $validation['total_rate.'.$base] =   ($total[$base] != 100 ) ? 'required' :  '' ;
                }
            }
			
			
            $request->validate(@$validation,[
				'total_rate.*.required' => 'Total Percentages Must be 100%',
				
            ]);
            QuantityCollectionSetting::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'collection_base' => $request->collection_base,
                    'general_collection' => $request->collection_base == 'general_collection_policy' ? $request->general_collection : null,
                    'first_allocation_collection' => $request->collection_base == $first_allocation_setting_base ? $request->first_allocation_collection : null,
                    'second_allocation_collection' => $request->collection_base == $second_allocation_setting_base ? $request->second_allocation_collection : null,
                ]
            );
            return redirect()->route('collection.quantity.report', $company);

        }
        $collection_settings= QuantityCollectionSetting::company()->first() ;

        $first_allocation_base_items = QuantityExistingProductAllocationBase::company()->first()->existing_products_target ?? formatExistingFormNewAllocation(QuantityNewProductAllocationBase::company()->first());
        $second_allocation_base_items = QuantitySecondExistingProductAllocationBase::company()->first()->existing_products_target ?? formatExistingFormNewAllocation(QuantitySecondExistingProductAllocationBase::company()->first());
        $sales_forecast = QuantitySalesForecast::company()->first();
        return view('client_view.quantity_forecast.collection_settings', compact(
            'company',
            'sales_forecast',
            'collection_settings',
            'first_allocation_base_items',
            'second_allocation_base_items',
            'first_allocation_setting_base',
            'second_allocation_setting_base'
        ));
    }


    public function collectionReport(Request $request, Company $company,$result ='view')
    {
        $collection_settings= QuantityCollectionSetting::company()->first() ;
        $first_allocation_setting_base = QuantityAllocationSetting::company()->first()->allocation_base?? null;
        $second_allocation_setting_base = QuantitySecondAllocationSetting::company()->first()->allocation_base?? null;

        $collection = [];
		// $collection_settings->collection_base = $first_allocation_setting_base;
        if ($collection_settings->collection_base == 'general_collection_policy') {
            $total_company_sales_target = (new QuantitySalesForecastReport)->productsAllocations($company,$request,'total_company_sales_target');
            $collection_data = $collection_settings->general_collection;
            $collection = $this->collectionCalculation($total_company_sales_target,$collection_data);


        } elseif ($collection_settings->collection_base == $first_allocation_setting_base) {

            $total_company_sales_target = (new QuantityAllocationsReport)->NewProductsSeasonality($request,$company,'total_company_sales_target');
            $collection_data = $collection_settings->first_allocation_collection;

            foreach ($total_company_sales_target as $base => $base_targets) {
                $collection[$base] = $this->collectionCalculation($base_targets,($collection_data[$base]??[]));
            }

        } elseif ($collection_settings->collection_base == $second_allocation_setting_base) {

            $total_company_sales_target = (new QuantitySecondAllocationsReport)->NewProductsSeasonality($request,$company,'total_company_sales_target');
            $collection_data = $collection_settings->second_allocation_collection;
            foreach ($total_company_sales_target as $base => $base_targets) {
                $collection[$base] = $this->collectionCalculation($base_targets,($collection_data[$base]??[]));
            }
        }
        // Months For Only First Year
        $sales_forecast = QuantitySalesForecast::company()->first();
        $monthly_dates = [];
        $counter = 1;
        for ($month = 0; $month < 12; $month++) {
            $date = $this->dateCalc($sales_forecast->start_date, $month);
            $monthly_dates[$date] = '';
            $counter++;
        }
        $forecast_year = date('Y',strtotime($sales_forecast->start_date));
        if ($result == 'view') {
            return view('client_view.quantity_forecast.collection_report',compact('company',
            'forecast_year',
            'monthly_dates',
            'collection',
            'collection_settings')
            );
        }else{
            return [
                'forecast_year' => $forecast_year,
                'monthly_dates' => $monthly_dates,
                'collection' => $collection,
                'collection_settings' => $collection_settings,
            ];
        }
    }

    public function collectionCalculation($targets,$collection_data)
    {

        $collection = [];
        foreach ($targets as $date => $target) {
            foreach ($collection_data as $key => $data) {
                $rate = ($data['rate']??0) / 100 ;

                $daysNumber = $data['due_days']??0 ;
                $days = $data['due_days']??0 ;
                $actualMonthsNumbers = $custom_months =  $days < 30 ?  0 : round((($data['due_days']??0)/30)) ;

                $date = (Carbon::make($date))->addMonths($actualMonthsNumbers);

                $month = $date->format('m');

                $year = $date->format('Y');
                $main_date = $date ;

                $fullDate = '01-'.$month.'-'.$year ;

                $collection[$fullDate] = ($target * $rate) + ($collection[$fullDate]??0) ;

            }

        }
        return $this->finalTotal($collection);

    }
}
