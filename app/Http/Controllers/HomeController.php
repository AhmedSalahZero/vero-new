<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Analysis\SalesGathering\CustomersNaturesAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\DiscountsAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\IncomeStatementBreakdownAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\IntervalsComparingForIncomeStatementReport;
use App\Http\Controllers\Analysis\SalesGathering\IntervalsComparingReport;
use App\Http\Controllers\Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport;
use App\Jobs\CheckDueAndPastedInvoicesJob;
use App\Jobs\ImportForeignExchangeRates;
use App\Jobs\ReactiveCurrentAccountStatement;
use App\Models\Company;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use App\Models\Log;
use App\Services\Caching\CashingService;
use App\Traits\GeneralFunctions;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
	use GeneralFunctions;

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function index(Request $request)
	{
	
		$user =  Auth::user();
		$companies = $user->companies;
		if (count($user->companies) > 1) {
			return view('client_view.home', compact('companies'));
		} else {
			if(count($user->companies) == 0){
				auth()->logout();
				return redirect()->route('login');
			}
			$company = $user->companies[0];
			return view('client_view.homePage', compact('company'));
		}
	}
	public function redirectFun(Company $company)
	{
		return   redirect()->route('viewHomePage', [$company]);
	}
	public function welcomePage(Request $request, Company $company)
	{
		if($company->hasCashVero()){
			dispatch_now(new ReactiveCurrentAccountStatement($company->id));
		}
		if($company->hasCashVero()){
			dispatch_now(new CheckDueAndPastedInvoicesJob($company->id));
		}
		if($company->hasOdooIntegrationCredentials()){
			dispatch_now(new ImportForeignExchangeRates($company->id));
		}
		
		return view('client_view.homePage', compact('company'));
	}

	public function dashboard(Request $request, Company $company)
	{
		$initialDates = getEndYearBasedOnDataUploaded($company) ;
		
		$start_date = $initialDates['jan'];
		$end_date   = $initialDates['dec'];

		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Dashboard'));
			$request['start_date'] = $start_date;
			$request['end_date'] = $end_date;
		} elseif ($request->isMethod('POST')) {
			$start_date = $request['start_date'];
			$end_date = $request['end_date'];
		}
		$end_date_month = date('m-Y', strtotime($end_date));
		$formated_end_date =  date('d-m-Y', strtotime($end_date));
		$previous_month = $this->dateCalc(date('01-m-Y', strtotime($end_date)), -1);
		$formated_previous_month =  date('d-m-Y', strtotime($previous_month));
		$previous_month = date('m-Y', strtotime($previous_month));
		$currentMonth = explode('-', $end_date_month)[0];
		$currentYear = explode('-', $end_date_month)[1];
		$daySales = DB::select(DB::raw(
			"
            select sum(net_sales_value) as day_sales from sales_gathering where date = '" . $end_date . "' and company_id = 
            " . $company->id
		));

		$daySales = $daySales[0]->day_sales ?: 0;

		$currentMonthSales = DB::select(DB::raw(

			"
            select sum(net_sales_value) as current_month_sales from sales_gathering where Year = " . $currentYear . " and Month=" . $currentMonth . " and company_id = 
            " . $company->id
		));

		$currentMonthSales = $currentMonthSales[0]->current_month_sales ?: 0;
		$previousMonth = Carbon::make($end_date)->startOfMonth()->subMonths(1)->month;

		$previousMonthYear = Carbon::make($end_date)->startOfMonth()->subMonths(1)->year;
		$previous2Month = Carbon::make($end_date)->startOfMonth()->subMonths(2)->month;
		$previous2MonthYear = Carbon::make($end_date)->startOfMonth()->subMonths(2)->year;
		$previous3Month = Carbon::make($end_date)->startOfMonth()->subMonths(3)->month;
		$previous3MonthYear = Carbon::make($end_date)->startOfMonth()->subMonths(3)->year;


		$perviousMonthSales = DB::select(DB::raw(

			"
            select sum(net_sales_value) as previous_month_sales from sales_gathering where Year = " . $previousMonthYear . " and Month=" . $previousMonth . " and company_id = 
            " . $company->id
		));
		$salesToDate = DB::select(DB::raw(
			"select sum(net_sales_value) total_sales_to_date from sales_gathering where date >= '" . $start_date . "' and date <= '" . $end_date . "' and company_id = " . $company->id
		));

		$perviousThreeMonthsSales = DB::select(DB::raw(
			"select sum(net_sales_value ) previous_three_months_sales from sales_gathering 
            where (
            (Year  =  " . $previousMonthYear  . " and Month=  " . $previousMonth . " ) 
            OR 
            (Year  = " . $previous2MonthYear  . " and Month= " . $previous2Month . ") 
            OR 
            (Year  = " .  $previous3MonthYear  . " and Month= " . ($previous3Month) . ") 
            )
                and company_id = " . $company->id
		));

		$perviousThreeMonthsSales = $perviousThreeMonthsSales[0]->previous_three_months_sales ?: 0;

		$salesToDate = $salesToDate[0]->total_sales_to_date ?: 0;

		$previous_month_sales = $perviousMonthSales[0]->previous_month_sales;

		$percentage = $previous_month_sales && $previous_month_sales != 0 ? ((($currentMonthSales - $previous_month_sales) / $previous_month_sales) * 100)   : 0;
		$monthlyChart = $this->formatMonthlyChars($company, $start_date, $end_date);
		$monthlyChartArr =  $monthlyChart['formattedData'];
		$monthlyChartCumulative =  $monthlyChart['cumulative'];
		$formattedDataForChart =  $monthlyChart['formattedDataForChart'];

		return view(
			'client_view.home_dashboard.dashboard',
			compact(
				'company',
				'start_date',
				'end_date',
				'daySales',
				'currentMonthSales',
				'previous_month_sales',
				'perviousThreeMonthsSales',
				'percentage',
				'salesToDate',
				'monthlyChartArr',
				'monthlyChartCumulative',
				'formattedDataForChart'
			)
		);
	}

	public function incomeStatementDashboard(Request $request, Company $company)
	{
		$start_date = date('2021-01-01');
		$end_date   = date('2021-12-31');

		if ($request->isMethod('GET')) {
			$request['start_date'] = $start_date;
			$request['end_date'] = $end_date;
		} elseif ($request->isMethod('POST')) {
			$start_date = $request['start_date'];
			$end_date = $request['end_date'];
		}
		$end_date_month = date('m-Y', strtotime($end_date));
		$formated_end_date =  date('d-m-Y', strtotime($end_date));
		$previous_month = $this->dateCalc(date('01-m-Y', strtotime($end_date)), -1);
		$formated_previous_month =  date('d-m-Y', strtotime($previous_month));
		$previous_month = date('m-Y', strtotime($previous_month));
		$currentMonth = explode('-', $end_date_month)[0];
		$currentYear = explode('-', $end_date_month)[1];
		// $daySales = DB::select(DB::raw(
		//     "
		//     select sum(net_sales_value) as day_sales from sales_gathering where date = '". $end_date ."' and company_id = 
		//     " . $company->id  
		// ));
		//  $incomeStatement = IncomeStatement::find($request->get('income_statement_id')) ?: IncomeStatement::where('company_id',$company->id)->latest()->first();
		//  if(!$incomeStatement){
		//          return redirect()->back()->with('fail',__('Please Create Income Statement First'));

		//  }


		$currentMonthSales = DB::select(DB::raw(
			"
            select sum(net_sales_value) as current_month_sales from sales_gathering where Year = " . $currentYear . " and Month=" . $currentMonth . " and company_id = 
            " . $company->id
		));
		$currentMonthSales = $currentMonthSales[0]->current_month_sales ?: 0;
		$previousMonth = Carbon::make($end_date)->startOfMonth()->subMonths(1)->month;

		$previousMonthYear = Carbon::make($end_date)->startOfMonth()->subMonths(1)->year;
		$previous2Month = Carbon::make($end_date)->startOfMonth()->subMonths(2)->month;
		$previous2MonthYear = Carbon::make($end_date)->startOfMonth()->subMonths(2)->year;
		$previous3Month = Carbon::make($end_date)->startOfMonth()->subMonths(3)->month;
		$previous3MonthYear = Carbon::make($end_date)->startOfMonth()->subMonths(3)->year;


		$perviousMonthSales = DB::select(DB::raw(
			"
            select sum(net_sales_value) as previous_month_sales from sales_gathering where Year = " . $previousMonthYear . " and Month=" . $previousMonth . " and company_id = 
            " . $company->id
		));
		$salesToDate = DB::select(DB::raw(
			"select sum(net_sales_value) total_sales_to_date from sales_gathering where date >= '" . $start_date . "' and date <= '" . $end_date . "' and company_id = " . $company->id
		));

		$perviousThreeMonthsSales = DB::select(DB::raw(
			"select sum(net_sales_value ) previous_three_months_sales from sales_gathering 
            where (
            (Year  =  " . $previousMonthYear  . " and Month=  " . $previousMonth . " ) 
            OR 
            (Year  = " . $previous2MonthYear  . " and Month= " . $previous2Month . ") 
            OR 
            (Year  = " .  $previous3MonthYear  . " and Month= " . ($previous3Month) . ") 
            )
                and company_id = " . $company->id
		));

		$perviousThreeMonthsSales = $perviousThreeMonthsSales[0]->previous_three_months_sales ?: 0;

		$salesToDate = $salesToDate[0]->total_sales_to_date ?: 0;

		$previous_month_sales = $perviousMonthSales[0]->previous_month_sales;

		$percentage = $previous_month_sales && $previous_month_sales!=0 ? ((($currentMonthSales - $previous_month_sales) / $previous_month_sales) * 100)   : 0;
		$monthlyChart = $this->formatMonthlyChars($company, $start_date, $end_date);
		$monthlyChartArr =  $monthlyChart['formattedData'];
		$monthlyChartCumulative =  $monthlyChart['cumulative'];
		$formattedDataForChart =  $monthlyChart['formattedDataForChart'];

		return view(
			'client_view.home_dashboard.income_statement_revenue_dashboard',
			compact(
				'company',
				'start_date',
				'end_date',
				'currentMonthSales',
				'previous_month_sales',
				'perviousThreeMonthsSales',
				'percentage',
				'salesToDate',
				'monthlyChartArr',
				'monthlyChartCumulative',
				'formattedDataForChart'
			)
		);
	}


	public function formatMonthlyChars($company, $start_date, $end_date)
	{
		$months = DB::select(DB::RAW(
			"select sum(net_sales_value) 'Sales Values' , month , count(*) as dd, year  , concat(date_format(LAST_DAY(concat(year , '-' ,month ,'-',1)) , '%d') , '-', MONTHNAME(concat(year , '-' ,month ,'-',1)) , '-', year  ) as date  
         from sales_gathering where company_id = " . $company->id . " 
            and  date between '" . $start_date . "' and '" . $end_date . "' group by month , year  order by 'sales values' desc"
		));
		$totalSums = array_sum(array_column($months, 'Sales Values'));

		$formattedData = [];
		$cumulative = [];
		$reportData['Monthly Sales'] = null;
		$reportData['Month Sales %'] = null;
		$reportData['Accumulated Sales'] = null;
		$reportData['Accumulated Sales'] = null;
		$reportData['Sales Values'] = null;
		$formattedDataForChart = [];


		for ($i = 0; $i < count($months); $i++) {
			$isFirstMonth = $i == 0 ;
			// for($i = 0 ; $i<count($months) ; $i++){
			$monthSales = $totalSums  ? number_format((($months[$i]->{'Sales Values'} / $totalSums) * 100), 1) : 0;
			$growthRate = 0 ;
			
			$previousMonthSalesValue = isset($months[$i - 1]) ?  $months[$i - 1]->{'Sales Values'} : 0 ;
			$growthRate =  $isFirstMonth || !$months[$i]->{'Sales Values'} || $previousMonthSalesValue==0 ? 0 : number_format((($months[$i]->{'Sales Values'} - $previousMonthSalesValue) / $previousMonthSalesValue) * 100, 1);
			$accumulatedSalesValue = $isFirstMonth ? $months[$i]->{'Sales Values'} : $months[$i]->{'Sales Values'} + $cumulative[$i - 1]['price'];

			$formattedDataForChart[$i]['Sales Values'] = $months[$i]->{'Sales Values'};
			$formattedDataForChart[$i]['date'] = $months[$i]->date;
			$formattedDataForChart[$i]['Month Sales %'] = $monthSales;
			$formattedDataForChart[$i]['Growth Rate %'] = $growthRate;

			$formattedData['Sales Values / Month'][$i] = $months[$i]->{'date'};
			$formattedData['Monthly Sales'][$i] = null;
			$formattedData['Sales Values'][$i] = number_format($months[$i]->{'Sales Values'}) . ' <span class="spanSales active-text-color font-weight-bold"> [ ' . $growthRate   . ' % ] </span>';
			$formattedData['Month Sales %'][$i] = $monthSales . ' %';
			$formattedData['Accumulated Sales'][$i] = null;
			$formattedData['Sales Values '][$i] = number_format($accumulatedSalesValue);
			$cumulative[$i]['date'] = $months[$i]->date;
			$cumulative[$i]['price'] = $accumulatedSalesValue;
		}
		if ($i > 0) {
			$formattedData['Sales Values'][$i] = number_format($accumulatedSalesValue);
			$formattedData['Month Sales %'][$i] = '100 %';
			$formattedData['Sales Values '][$i] = '-';
		}


		return [
			'cumulative' => $cumulative,
			'formattedData' => $formattedData,
			'formattedDataForChart' => $formattedDataForChart
		];
	}
	protected function setBarColorsForTypes(array $types)
	{
		$newTypes = [];
		$firstClass = 'brand';

		
		$previousColor = null ;
		foreach($types as $type => $oldColor){
			$color = [
				$firstClass=>'success',
				'success'=>'danger',
				'danger'=>$firstClass,
				null=>$firstClass
			][$previousColor] ;
			if(!is_null($previousColor)){
			}
			
			$newTypes[$type]=$color;
			$previousColor = $color ;
		}
		return $newTypes;
		
	}
	public function dashboardBreakdownAnalysis(Request $request, Company $company)
	{
		if($company->isCachingNow()){
			return redirect()->route('viewHomePage',['company'=>$company->id ])->with('fail',__('Please Wait Until Breakdown Dashboard Recalculate'));
		}
		$simpleLinearRegressionForAllTypes=[];
		$simpleLinearRegressionDatesForAllTypes=[];
		$initialDates = getEndYearBasedOnDataUploaded($company);
		$start_date = $initialDates['jan'];
		$end_date   = $initialDates['dec'];
		$simpleLinearRegressionDates = [];
		
		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Breakdown Dashboard'));
			$request['start_date'] = $start_date;
			$request['end_date'] = $end_date;
		} elseif ($request->isMethod('POST')) {
			$start_date = $request['start_date'];
			$end_date = $request['end_date'];
		}
		$exportableFields  = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');
		$db_names = array_keys($exportableFields);
		$types =  [
			'zone' => 'brand',
			'sales_channel' => 'warning',
			'branch' => 'danger',
			'category' => 'success',
			'product_or_service' => 'brand',
			'product_item' => 'warning',
			'business_sector' => 'brand',
			'business_unit' => 'success',
			'service_provider_name' => 'warning',
			'service_provider_type' => 'danger',
			'service_provider_birth_year' => 'success',
			'country'=>'danger',
			'principle'=>'danger'
		];
		$reports_data = [];
		$simpleLinearRegressionDates = [];
		$top_data = [];
		$simpleLinearRegressionForCompany = [];
		$breakdown_data = [];
		foreach ($types as  $type => $color) {
			if (false !== $found = array_search($type, $db_names)) {
				$request['type'] = $type;
				$cacheKeyName = getBreakdownCacheNameForCompanyAndDatesAndType($company,$start_date,$end_date, $type);
				$cacheForSimpleLinearRegressionKeyName = \getBreakdownSimpleLinearRegressionCacheNameForCompanyAndDatesAndType($company,$start_date,$end_date, $type);
				$cacheForSimpleLinearRegressionForCompanyKeyName = \getBreakdownSimpleLinearRegressionCacheNameFor2CompanyAndDatesAndType($company,$start_date,$end_date, $type);
				$cacheForSimpleLinearRegressionDatesKeyName = getBreakdownSimpleLinearRegressionDatesCacheNameForCompanyAndDatesAndType($company,$start_date,$end_date, $type);
				
				
				if (!Cache::has($cacheKeyName)) {
					$breakdown_data_with_simple_linear_regression = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'array_with_ai');
					$breakdown_data = $breakdown_data_with_simple_linear_regression['report_view_data']??[];
					$simpleLinearRegression = $breakdown_data_with_simple_linear_regression['simple_linear_regression']??[];
					$simpleLinearRegressionForCompany = $breakdown_data_with_simple_linear_regression['ai_for_company']??[];
					$simpleLinearRegressionDates = $breakdown_data_with_simple_linear_regression['simple_linear_regression_dates']??[];
					Cache::forever($cacheKeyName, $breakdown_data);
					Cache::forever($cacheForSimpleLinearRegressionKeyName, $simpleLinearRegression);
					Cache::forever($cacheForSimpleLinearRegressionForCompanyKeyName, $simpleLinearRegressionForCompany);
					Cache::forever($cacheForSimpleLinearRegressionDatesKeyName, $simpleLinearRegressionDates);
				} else {
					$breakdown_data = Cache::get($cacheKeyName);
					$simpleLinearRegression = Cache::get($cacheForSimpleLinearRegressionKeyName);
			
					$simpleLinearRegressionForCompany = Cache::get($cacheForSimpleLinearRegressionForCompanyKeyName);
					$simpleLinearRegressionDates = Cache::get($cacheForSimpleLinearRegressionDatesKeyName);
				}

				if ($type == 'service_provider_birth_year' || $type == 'service_provider_type') {
					$first_item = collect($breakdown_data['report_view_data']??[])->sortByDesc(function ($data, $key) {
						return [$data['Sales Value']];
					})->toArray();
					$first_item = ($first_item ?? []);
					$top_data[$type] = array_shift($first_item);
				} else {
					$top_data[$type] = $breakdown_data[0] ?? '-';
				}
				$reports_data[$type] = $breakdown_data;
				$simpleLinearRegressionForAllTypes[$type]= $simpleLinearRegression;
				foreach($simpleLinearRegressionDates as $date){
					$simpleLinearRegressionForAllTypes[$type]['total'][$date] = $this->getTotal($simpleLinearRegression,$date);
				}
			
				$simpleLinearRegressionDatesForAllTypes[$type]= $simpleLinearRegressionDates;
			} else {
				unset($types[$type]);
			}
		}
		
		$types = $this->setBarColorsForTypes($types);

		return view('client_view.home_dashboard.dashboard_breakdown', compact(
			'company',
			'reports_data',
			'simpleLinearRegressionForAllTypes',
			'simpleLinearRegressionDatesForAllTypes',
			'simpleLinearRegressionForCompany',
			'types',
			'top_data',
			'start_date',
			'end_date'
			// ,'fullReport','topes'
		));
	}
	protected function getTotal(array $items , string $date){
		$total = 0 ;
		foreach($items as $item){
			$current = isset($item[$date]) ? $item[$date] : 0;
			$total += $current;
		}
		return $total;
	}

	public function dashboardBreakdownIncomeStatementAnalysis(Request $request, Company $company, $incomeStatementID = null)
	{
		$isComparingReport = true;
		$reportType = $request->segments()[4];
		if (in_array($reportType, getAllFinancialAbleTypes())) {
			$comparingReport = false;
		} elseif ($reportType != 'comparing') {
			abort(404);
		}
		$incomeStatement = IncomeStatement::find($request->get('income_statement_id') ?: $incomeStatementID) ?: IncomeStatement::where('company_id', $company->id)->latest()->first();
		$start_date = $incomeStatement ? $incomeStatement->getFirstAndEndDate()['start_date'] : date('2022-01-01');
		$end_date   =  $incomeStatement ? $incomeStatement->getFirstAndEndDate()['end_date'] : date('2022-12-31');
		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Income Statement Dashboard'));
			
			$request['start_date'] = $start_date;
			$request['end_date'] = $end_date;
		} elseif ($request->isMethod('POST')) {
			$start_date = $request['start_date'];
			$end_date = $request['end_date'];
		}
		$exportableFields  = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');
		$db_names = array_keys($exportableFields);
		$types =  [
			'zone' => 'brand',
			'sales_channel' => 'warning',
			'branch' => 'danger',
			'category' => 'success',
			'product_or_service' => 'brand',
			'product_item' => 'warning',
			'business_sector' => 'brand',
			'service_provider_name' => 'warning',
			'service_provider_type' => 'danger',
			'service_provider_birth_year' => 'success',
		];
		$reports_data = [];
		$top_data = [];
		$incomeStatement = IncomeStatement::find($request->get('income_statement_id') ?: $incomeStatementID) ?: IncomeStatement::where('company_id', $company->id)->latest()->first();
		if ($incomeStatement) {
			$breakdown_data = (new IncomeStatementBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, $incomeStatement, $reportType, $isComparingReport);
		} else {
			$incomeStatement = optional();
			$breakdown_data   = [];
		}
		$formattedDataForChart = formatDataForChart($breakdown_data);
		$monthlyChartCumulative = getMonthlyChartCumulative($formattedDataForChart);
		$types = array_unique(array_keys($breakdown_data));
		$types = \array_fill_keys_with_values($types);
		$reports_data = $breakdown_data;
		$corporateTaxesPercentage =$incomeStatement && $incomeStatement->getSubItems(0, $reportType) ? $incomeStatement->getSubItems(0, $reportType)->where('id',IncomeStatementItem::CORPORATE_TAXES_ID)->first()->pivot->percentage_value : 0;
		return view('client_view.home_dashboard.dashboard_breakdown_incomestatement', compact(
			'company',
			'formattedDataForChart',
			'monthlyChartCumulative',
			'incomeStatement',
			'reports_data',
			'types',
			'top_data',
			'start_date',
			'end_date',
			'corporateTaxesPercentage',
			// ,'fullReport','topes'
		));
	}



	

	public function dashboardCustomers(Request $request, Company $company)
	{
		if($company->isCachingNow()){
			return redirect()->route('viewHomePage',['company'=>$company->id ])->with('fail',__('Please Wait Until Customer Dashboard Recalculate'));
		}
		$cashingService = new CashingService($company);
		$years = $cashingService->getIntervalYearsFormCompany();
		if ($request->isMethod('GET') && isset($years['full_end_date']) &&$years['full_end_date']) {
			$date   = $years['full_end_date'] ;
		} else {
			$date = $years['full_end_date']??null;
		}

		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Customer Dashboard'));

			$request['date'] = $date;
		} elseif ($request->isMethod('POST')) {
			$date = $request['date'];
		}
		$request['type'] = 'customer_name';

		$request['start_date'] = date('Y', strtotime($date)) . '-01-01';
		$request['end_date'] = $date;

		$request['type'] = 'customer_nature';
		$request['date'] = $date;

		$customers_natures = (new CustomersNaturesAnalysisReport)->result($request, $company, 'array');
		
		/**
		 * test 
		 */
		
		$totals = $customers_natures['totals'];
		$sum = 0 ;
		foreach($totals as $total){
			$sum+= $total->val ;
		}
		

		return view('client_view.home_dashboard.dashboard_customers', compact('company', 'customers_natures', 'date'));
	}
	public function dashboardSalesPerson(Request $request, Company $company)
	{
		$initialDates = getEndYearBasedOnDataUploaded($company);
		$start_date = $initialDates['jan'];
		$end_date   = $initialDates['dec'];

		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Sales Person Dashboard'));

			$request['start_date'] = $start_date;
			$request['end_date'] = $end_date;
		} elseif ($request->isMethod('POST')) {
			$start_date = $request['start_date'];
			$end_date = $request['end_date'];
		}
		$request['type'] = 'sales_person';
		$sale_person =  (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $company, 'array');


		return view('client_view.home_dashboard.dashboard_salesPerson', compact('company', 'sale_person', 'start_date', 'end_date'));
	}
	public function dashboardSalesDiscount(Request $request, Company $company)
	{
		$initialDates = getEndYearBasedOnDataUploaded($company);
		$start_date = $initialDates['jan'];
		$end_date   = $initialDates['dec'];

		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Sales Discount Dashboard'));
			
			$request['start_date'] = $start_date;
			$request['end_date'] = $end_date;
		} elseif ($request->isMethod('POST')) {
			$start_date = $request['start_date'];
			$end_date = $request['end_date'];
		}

		$sales_discount_bd = (new SalesBreakdownAgainstAnalysisReport)->discountsSalesBreakdownAnalysisResult($request, $company, 'array');

		$request['main_type'] = 'sales_channel';

		$sales_channels_discounts = (new DiscountsAnalysisReport)->result($request, $company, 'array');
		// [$report_data,$all_items,$items_totals]
		return view('client_view.home_dashboard.dashboard_salesDiscount', compact('company', 'sales_discount_bd', 'sales_channels_discounts', 'start_date', 'end_date'));
	}
	public function dashboardIntervalComparing(Request $request, Company $company)
	{
		$initialDates = getEndYearBasedOnDataUploaded($company);
		$start_date_0 = $initialDates['jan'];
		$end_date_0   = $initialDates['dec'];
		
		$initialDates = getEndYearBasedOnDataUploaded($company,1);
		$start_date_1 = $initialDates['jan'];
		$end_date_1   = $initialDates['dec'];


		$allTypes =  [
			'zone' => 'brand',
			'sales_channel' => 'warning',
			'branch' => 'danger',
			'category' => 'success',
			'product_or_service' => 'brand',
			'product_item' => 'warning',
			'business_sector' => 'brand',
			'service_provider_name' => 'warning',
			'service_provider_type' => 'danger',
			'service_provider_birth_year' => 'success',
			'customer_name' => 'success',
			'country'=>'warning'
		];

		$exportableFields  = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');
		$db_names = array_keys($exportableFields);

		$permittedTypes = [];
		foreach ($allTypes as  $type => $color) {
			if (false !== $found = array_search($type, $db_names)) {
				$permittedTypes[$type] = camelize($type);
			}
		}
		$selectedTypes = (array)$request->types;
		$intervalComparing = [];


		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Interval Comparing Dashboard'));
			
			$keys = array_keys($permittedTypes);
			$firstKey = $keys[0] ?? 0;
			// $secondKey = $keys[1] ?? 0;
			// $thirdKey = $keys[2] ?? 0;

			$request['types'] = [
				$firstKey
				// , $secondKey
				// ,$thirdKey 
			];
			$request['start_date_one'] = $start_date_0;
			$request['end_date_one'] = $end_date_0;
			$request['start_date_two'] = $start_date_1;
			$request['end_date_two'] = $end_date_1;

			//  $request['start_date_three'] = $start_date_2;
			// $request['end_date_three'] = $end_date_2;

		} elseif ($request->isMethod('POST')) {


			$start_date_0  = $request['start_date_one'];
			$end_date_0  = $request['end_date_one'];
			$start_date_1  = $request['start_date_two'];
			$end_date_1  = $request['end_date_two'];
			// $start_date_2  = $request['start_date_three'];
			// $end_date_2  = $request['end_date_three'];
		}

		foreach ((array)$request->types as $t) {
			$request['type'] = $t;
			$intervalComparing[$t] = (new IntervalsComparingReport)->result($request, $company, 'array');
		}

		
		$latestReport = null ;
		if(Carbon::make($end_date_1)->greaterThan(Carbon::make($end_date_0))){
			$latestReport =    '_two' ;
		}elseif(Carbon::make($end_date_0)->greaterThan(Carbon::make($end_date_1))){
			$latestReport =    '_one' ;
		}
		
		$customers_natures = [];

		return view('client_view.home_dashboard.dashboard_intervalComparing', compact(
			'company',
			'latestReport'
			// ,'product_items','sales_channels'
			,
			'start_date_0',
			'end_date_0',
			'start_date_1',
			'end_date_1',
			// 'start_date_2', 'end_date_2',

			'permittedTypes',
			'selectedTypes',
			'intervalComparing'
		));
	}


	public function dashboardIncomeStatementIntervalComparing(Request $request, Company $company)
	{
		$canRefreshDates = false ; 
		$firstReportType = $request->has('first_report_type') ? $request->get('first_report_type') : 'forecast';
		$secondReportType = $request->has('second_report_type') ? $request->get('second_report_type') : 'forecast';

		$incomeStatements  = IncomeStatement::where('company_id', $company->id)->get();
		if (!(count($incomeStatements) >= 1)) {
			return redirect()->back()->with('fail', __('You Must Have At Least One Income Statements'));
		}

		$firstIncomeStatement = $incomeStatements[0];
		$secondIncomeStatement = $incomeStatements[1] ?? null;

		$firstIncomeStatementId = $request->get('financial_statement_able_first_interval');
		$secondIncomeStatementId = $request->get('financial_statement_able_second_interval');
		if ($firstIncomeStatementId) {
			$firstIncomeStatement =     IncomeStatement::find($firstIncomeStatementId);
		}
		if ($secondIncomeStatementId) {
			$secondIncomeStatement = IncomeStatement::find($secondIncomeStatementId);
		}
		$initialDates = getEndYearBasedOnDataUploaded($company);
		$start_date_0 = $initialDates['jan'];
		$end_date_0   = $initialDates['dec'];
		
		$initialDates = getEndYearBasedOnDataUploaded($company,1);
		$start_date_1 = $initialDates['jan'];
		$end_date_1   = $initialDates['dec'];

		$start_date_0 = $firstIncomeStatement ? $firstIncomeStatement->getFirstAndEndDate()['start_date'] : $start_date_0;
		$end_date_0   = $firstIncomeStatement ? $firstIncomeStatement->getFirstAndEndDate()['end_date'] : $end_date_0;
		$start_date_1 = $secondIncomeStatement ? $secondIncomeStatement->getFirstAndEndDate()['start_date'] : $start_date_1;
		$end_date_1   = $secondIncomeStatement ? $secondIncomeStatement->getFirstAndEndDate()['end_date'] : $end_date_1;
		
		$secondIncomeStatement = $secondIncomeStatement ?: optional(null);
		$allTypes = IncomeStatementItem::formattedViewForDashboard();
		$exportableFields  = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');
		$db_names = array_keys($exportableFields);

		$permittedTypes = [];
		foreach ($allTypes as  $id => $name) {
			$permittedTypes[$id] = ($name);
		}
		$selectedTypes = (array)$request->types;
		$intervalComparing = [];
		$selectAllOptions = false;

		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Income Statement Comparing'));
			
			$canRefreshDates = true ;
			$selectAllOptions = true;
			$keys = array_keys($permittedTypes);
			$firstKey = $keys[1] ?? 0;
			$secondKey = $keys[2] ?? 0;
			// $thirdKey = $keys[2] ?? 0;

			$request['types'] = [
				$firstKey, $secondKey
			];
			$request['start_date_one'] = $start_date_0;
			$request['end_date_one'] = $end_date_0;
			$request['start_date_two'] = $start_date_1;
			$request['end_date_two'] = $end_date_1;
			//  $request['start_date_three'] = $start_date_2;
			// $request['end_date_three'] = $end_date_2;

		} elseif ($request->isMethod('POST')) {

			$canRefreshDates = false ;
			$start_date_0  = $request['start_date_one'];
			$end_date_0  = $request['end_date_one'];
			$start_date_1  = $request['start_date_two'];
			$end_date_1  = $request['end_date_two'];
			// $start_date_2  = $request['start_date_three'];
			// $end_date_2  = $request['end_date_three'];
		}

		if (Carbon::make($end_date_0)->lessThan(Carbon::make($start_date_0))) {
			$start_date_wap = $start_date_0;
			$start_date_0 = $end_date_0;
			$end_date_0 = $start_date_wap;
		}
		if (Carbon::make($end_date_1)->lessThan(Carbon::make($start_date_1))) {
			$start_date_wap = $start_date_1;
			$start_date_1 = $end_date_1;
			$end_date_1 = $start_date_wap;
		}
		$intervalDates  = [
			'first_start_date' => $start_date_0,
			'first_end_date' => $end_date_0,
			'second_start_date' => $start_date_1,
			'second_end_date' => $end_date_1
		];

		foreach ((array)$request->types as $typeId) {
			$request['mainItemId'] = $typeId;
			$intervalComparing[IncomeStatementItem::where('id', $typeId)->first()->name] = (new IntervalsComparingForIncomeStatementReport)->result($request, $company, $intervalDates, $firstReportType, $secondReportType);
		}


		$selectedItems = [
			'first_report_type' => $firstReportType,
			'second_report_type' => $secondReportType
		];
		$intervals = getIntervals($intervalComparing);
		$selectedTypesIndexes = [
			$firstReportType, $secondReportType
		];
		return view('client_view.home_dashboard.dashboard_intervalComparing_income_statements', compact(
			'selectedItems',
			'company',
			'secondReportType',
			'selectedTypesIndexes',
			'start_date_0',
			'end_date_0',
			'start_date_1',
			'end_date_1',
			'incomeStatements',
			'firstIncomeStatement',
			'secondIncomeStatement',
			'intervals',
			'intervalDates',
			'selectAllOptions',
			// 'start_date_2', 'end_date_2',
			
			'permittedTypes',
			'selectedTypes',
			'intervalComparing',
			'canRefreshDates',
		));
	}



	public function dashboardIncomeStatementVariousComparing(Request $request, Company $company)
	{
		$canRefreshDates = false ; 
		$reportType = $request->get('report_type') ?: 'forecast-actual';
		$firstComparingType =  explode('-', $reportType)[0];
		$secondComparingType = explode('-', $reportType)[1];
		if ($firstComparingType == $secondComparingType) {
			return redirect()->back()->with('fail', __('Please Select Different Comparing Types'));
		}

		$incomeStatements  = IncomeStatement::where('company_id', $company->id)->get();
		if (!(count($incomeStatements) >= 1)) {
			return redirect()->back()->with('fail', __('You Must Have At Least One Income Statements'));
		}
		$incomeStatement = $incomeStatements[count($incomeStatements)-1];
		$incomeStatementId = $request->get('income_statement_id') ;
		if(!$incomeStatementId){
			$request['income_statement_id'] = IncomeStatement::where('company_id', $company->id)->latest()->first()->id ?? null ;
		}
		if ($incomeStatementId) {
			$incomeStatement =     IncomeStatement::find($incomeStatementId);
		}
		
		$start_date = $incomeStatement ? $incomeStatement->getFirstAndEndDate()['start_date'] : date('2021-01-01');
		$end_date   = $incomeStatement ? $incomeStatement->getFirstAndEndDate()['end_date'] : date('2021-12-31');
		if(! Carbon::make($end_date)){
			return redirect()->back()->with('fail', __('Please Enter End Date'));
		}
		if(! Carbon::make($start_date) ){
			return redirect()->back()->with('fail', __('Please Enter Start Date Date'));
		}
		$allTypes = IncomeStatementItem::formattedViewForDashboard();

		$exportableFields  = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');
		$db_names = array_keys($exportableFields);
		$permittedTypes = [];
		foreach ($allTypes as  $id => $name) {
			$permittedTypes[$id] = ($name);
		}
		$selectedTypes = count((array)$request->types) ? (array)$request->types : array_keys($permittedTypes);
		$intervalComparing = [];
		$requestMethod = $request->method();
		if ($request->isMethod('GET')) {
			Log::storeNewLogRecord('enterSection',null,__('Variance Dashboard'));
			$canRefreshDates = true ; 
			$keys = array_keys($permittedTypes);
			$firstKey = $keys[1] ?? 0;
			$secondKey = $keys[2] ?? 0;
			$request['types'] = $keys;
			$request['start_date'] = $start_date;
			$request['end_date'] = $end_date;
		} elseif ($request->isMethod('POST')) {
			$canRefreshDates = false  ; 
			$start_date  = $request['start_date'];
			$end_date  = $request['end_date'];
		}
		if (Carbon::make($end_date)->lessThan(Carbon::make($start_date))) {
			$start_date_wap = $start_date;
			$start_date = $end_date;
			$end_date = $start_date_wap;
		}

		$intervalDates  = [
			'start_date' => $start_date,
			'end_date' => $end_date,
		];
		$charts = [];
		$dates = [];
		$chartHasBeenFound = false;
		$chartItems = count((array)$request->get('chart_items')) ? (array)$request->get('chart_items') : addAllSubItemForMainItemsArray($allTypes, $incomeStatement, request()->segments()[4]);

		foreach ($selectedTypes as $typeId) {
			$request['mainItemId'] = $typeId;
			$dataOfVarious = (new IntervalsComparingForIncomeStatementReport)->resultVariousComparing($request, $company, $intervalDates, $firstComparingType, $secondComparingType, $chartHasBeenFound, IncomeStatementItem::where('id', $typeId)->first()->name, $chartItems);
			
			$intervalComparing[IncomeStatementItem::where('id', $typeId)->first()->name] = $dataOfVarious['report'];
			$charts[IncomeStatementItem::where('id', $typeId)->first()->name] = $dataOfVarious['charts'];
			if (!$chartHasBeenFound) {
				$chartHasBeenFound = true;
				$dates = $dataOfVarious['dates'];
			}
		}
		$charts = removeFirstKeyAndMergeOthers($charts);
		$incomeStatementId = $request->get('income_statement_id');
		if ($incomeStatementId) {
			$selectedIncomeStatement =     IncomeStatement::find($incomeStatementId);
		}
		$intervals = getIntervals($intervalComparing);
		$selectedItems = [
			'income_statement_id' => $incomeStatementId ?: $incomeStatement->id,
			'main_items' => $selectedTypes,
			'first_report_type' => $firstComparingType,
			'second_report_type' => $secondComparingType,
			'start_date' =>  $start_date,
			'end_date' =>   $end_date,
			'report_type' => $reportType

		];

		$mainItemsWithItemsSubItems = extractMainItemsAndSubItemsFrom($intervalComparing);
		
		
		return view('client_view.home_dashboard.dashboard_variousComparing_income_statements', compact(
			'chartItems',
			'reportType',
			'charts',
			'dates',
			'mainItemsWithItemsSubItems',
			'company',
			'selectedItems',
			'start_date',
			'end_date',
			'incomeStatements',
			'incomeStatement',
			'incomeStatement',
			'intervals',
			'intervalDates',
			'requestMethod',
			'permittedTypes',
			'selectedTypes',
			'intervalComparing',
			'canRefreshDates'
		));
	}
	public function getIncomeStatementStartDateAndEndDate(Request $request)
	{
		$income_statement_id = $request->get('income_statement_id');
		$incomeStatement  = IncomeStatement::where('id', $income_statement_id)->first();
		if ($incomeStatement) {
			$dates = $incomeStatement->getFirstAndEndDate();
			return response()->json([
				'status' => count($dates),
				'dates' => $dates
			]);
		}
	}
}
