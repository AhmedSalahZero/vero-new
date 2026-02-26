<?php

namespace App\Http\Controllers\Analysis\SalesGathering;

use App\Models\Company;
use App\Models\SalesGathering;
use App\Services\Caching\CustomerDashboardCashing;
use App\Services\Caching\CustomerNatureCashing;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomersNaturesAnalysisReport
{
	use GeneralFunctions;
	//     function formatDataForType(array $dataOfArray , string  $typeToCache)
	// {
	//       $formattedData=[];
	//            foreach($dataOfArray as $mainType=>$dataObj)
	//            {
	//                isset($formattedData[$mainType]) ?  array_push($formattedData[$mainType] , $dataObj ) : $formattedData[$mainType] = [$dataObj] ;
	//            }
	//            return $formattedData ;
	// }

	public function twoDimensionalResult(Request $request, Company $company, $result = 'view')
	{

		$report_data = [];
		$type = $request->type;
		$view_name = $request->view_name;
		$requested_date = $request->date;
		$first_date_of_current_year = date('Y-01-01', strtotime($request->date));
		$dates = [
			'start_date' => date('d-M-Y', strtotime($first_date_of_current_year)),
			'end_date' => date('d-M-Y', strtotime($request->date))
		];
		$all_items = [];


		$year = $request->date ? Carbon::make($request->date)->format('Y') : null;
		$month = $request->date ? Carbon::make($request->date)->format('m') : null;


		$customerDashboardCashing = new CustomerNatureCashing($company, $year,$month);

		$cashedResult = $customerDashboardCashing->cacheAll();

		$newCustomers = $cashedResult['newCustomersForType'][$type];

		$RepeatingCustomers = $cashedResult['RepeatingForType'][$type];
		$activeCustomers = $cashedResult['ActiveForType'][$type];
		$stopReactive = $cashedResult['StopReactivatedForType'][$type];
		$deadReactivatedCustomers = $cashedResult['deadReactivatedForType'][$type];
		$stopRepeatingCustomers = $cashedResult['StopRepeatingForType'][$type];
		$deadRepeatingCustomers = $cashedResult['DeadRepeatingForType'][$type];
		$stopCustomers = $cashedResult['StopForType'][$type];
		$deadCustomers = $cashedResult['DeadForType'][$type];
		$totalsCustomersPerType = $cashedResult['totalsForType'][$type];
		// $totals = $cashedResult['totals']; 
		$customersNaturesActive = $statics =
			[
				'New' => $newCustomers,
				'Repeating' => $RepeatingCustomers,
				'Active' => $activeCustomers,
				'Stop / Reactivated' => $stopReactive,
				'Dead / Reactivated' => $deadReactivatedCustomers,
				'Stop / Repeating' => $stopRepeatingCustomers,
				'Dead / Repeating' => $deadRepeatingCustomers,
			];

		$customersNaturesDead = [
			'Stop' => $stopCustomers,
			'Dead' => $deadCustomers,
		];

		//   testCalcs($customersNaturesActive);
		$last_date = null;

		if ($result == 'view') {
			$last_date = SalesGathering::company()->latest('date')->first()->date ?? '';
			$last_date = date('d-M-Y', strtotime($last_date));
			$date = $requested_date;
			// $reportDataFormatted = $this->formatCustomerAnalysisReport($customers_natures);

			return view('client_view.reports.sales_gathering_analysis.customer_nature.two_dimensional_report', compact(
				// 'reportDataFormatted',
				'last_date',
				'type',
				'view_name',
				'dates',
				'company',
				'date',
				'statics',
				'requested_date',
				'newCustomers',
				'customersNaturesActive',
				'customersNaturesDead',
				'totalsCustomersPerType'
			));
		} else {
			return array_merge(
				$report_data,
				$statics

			);
		}
	}

	public function index(Company $company)
	{

		if (request()->route()->named('customersNatures.analysis')) {
			$type = 'customer_nature';
			$view_name = 'Customers Natures Analysis';
		} elseif (request()->route()->named('zones.vs.customersNatures')) {
			$type = 'zone';
			$view_name = 'Zones Versus Customers Natures Analysis';
		} elseif (request()->route()->named('salesChannels.vs.customersNatures')) {
			$type = 'sales_channel';
			$view_name = 'Sales Channels Versus Customers Natures Analysis';
		} elseif (request()->route()->named('businessSectors.vs.customersNatures')) {
			$type = 'business_sector';
			$view_name = 'Business Sectors Versus Customers Natures Analysis';
		}elseif (request()->route()->named('businessUnits.vs.customersNatures')) {
			$type = 'business_unit';
			$view_name = 'Business Units Versus Customers Natures Analysis';
		} elseif (request()->route()->named('branches.vs.customersNatures')) {
			$type = 'branch';
			$view_name = 'Branches Versus Customers Natures Analysis';
		} elseif (request()->route()->named('categories.vs.customersNatures')) {
			$type = 'category';
			$view_name = 'Categories Versus Customers Natures Analysis';
		} elseif (request()->route()->named('products.vs.customersNatures')) {
			$type = 'product_or_service';
			$view_name = 'Products Versus Customers Natures Analysis';
		} elseif (request()->route()->named('Items.vs.customersNatures')) {
			$type = 'product_item';
			$view_name = 'Products Items Versus Customers Natures Analysis';
		} elseif (request()->route()->named('countries.vs.customersNatures')) {
			$type = 'country';
			$view_name = 'Countries Versus Customers Natures Analysis';
		}


		return view('client_view.reports.sales_gathering_analysis.customer_nature.sales_form', compact('company', 'view_name', 'type'));
	}


	public function result(Request $request, Company $company, $result = 'view')
	{
		$report_data = [];
		$type = $request->type;
		$view_name = $request->view_name;
		$requested_date = $request->date;
		$first_date_of_current_year = date('Y-01-01', strtotime($request->date));
		$dates = [
			'start_date' => date('d-M-Y', strtotime($first_date_of_current_year)),
			'end_date' => date('d-M-Y', strtotime($request->date))
		];
		$all_items = [];


		$year = $request->date ? Carbon::make($request->date)->format('Y') : null;
		$month = $request->date ? Carbon::make($request->date)->format('m') : null;
		if(!$year){
			return [];
		}
		$customerDashboardCashing = new CustomerDashboardCashing($company, $year,$month);

		$cashedResult = $customerDashboardCashing->cacheAll();

		$newCustomers = $cashedResult['newCustomers'];
		$RepeatingCustomers = $cashedResult['RepeatingCustomers'];
		$activeCustomers = $cashedResult['activeCustomers'];
		$stopReactive = $cashedResult['stopReactive'];
		$deadReactivatedCustomers = $cashedResult['deadReactivatedCustomers'];
		$stopRepeatingCustomers = $cashedResult['stopRepeatingCustomers'];
		$deadRepeatingCustomers = $cashedResult['deadRepeatingCustomers'];
		$stopCustomers = $cashedResult['stopCustomers'];
		$deadCustomers = $cashedResult['deadCustomers'];
		$totals = $cashedResult['totals'];
	
		$customers_natures = $statics = [
			'totals' => $totals,
			'statictics' => [
				'New' => $newCustomers,
				'Repeating' => $RepeatingCustomers,
				'Active' => $activeCustomers,
				'Stop / Reactivated' => $stopReactive,
				'Dead / Reactivated' => $deadReactivatedCustomers,
				'Stop / Repeating' => $stopRepeatingCustomers,
				'Dead / Repeating' => $deadRepeatingCustomers,
			],
			'stops' => [
				'Stop' => $stopCustomers,
				'Dead' => $deadCustomers,
			]
		];

		$last_date = null;

		// if($result == 'top_and_bottom_fifty')
		// {
		//     return [
		//         'top_50'=>array_slice($customers_natures['totals'] , 0 , 50 , true),
		//         'bottom_50'=>array_slice($customers_natures['totals'] , -50 , 50 , true),
		//     ];
		// }
		if ($result == 'view') {
			$last_date = SalesGathering::company()->latest('date')->first()->date ?? '';
			$last_date = date('d-M-Y', strtotime($last_date));
			$date = $requested_date;
			$reportDataFormatted = $this->formatCustomerAnalysisReport($customers_natures);
			return view('client_view.reports.sales_gathering_analysis.customer_nature.sales_report', compact('reportDataFormatted', 'last_date', 'type', 'view_name', 'dates', 'company', 'date', 'statics', 'requested_date', 'newCustomers', 'customers_natures'));
		} else {
			return array_merge(
				$report_data,
				$statics
			);
		}
	}
	private function formatCustomerAnalysisReport($customers_natures)
	{
		$data = array_merge($customers_natures['statictics'], $customers_natures['stops']);

		$reportData = [
			'Counts' => [],
			'Total Sales Values' => []
		];
		$allTypesCount =   array_sum(array_map("count", $data));


		foreach ($data as $customerType => $values) {
			$totalSumOfType = 0;
			$reportData['Counts'][] = ['name' => $customerType, 'val' => $allTypesCount ? (count($values) / $allTypesCount) * 100 : 0];
			foreach ($data[$customerType] as $singleCustomerOfType) {
				$totalSumOfType += $singleCustomerOfType->total_sales;
			}
			$reportData['Total Sales Values'][] = ['name' => $customerType, 'val' => $totalSumOfType];
		}

		return $reportData;
	}


}
