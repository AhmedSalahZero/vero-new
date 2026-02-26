<?php

namespace App\Services\Caching;

use App\Http\Controllers\Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BreakdownCashing
{
	private Company $company;
	private string $current_start_date;
	private string $current_end_date;
	private string $current_end_year;
	private string $year;
	private string $end_year;
	private array $typesOfCaching;

	public function __construct(Company $company, string $year,string $end_year)
	{
		$this->company = $company;
		$this->year = $year;
		$this->end_year = $end_year;
		$this->current_start_date = $year.'-01-01';
		$this->current_end_date = $year.'-12-31';
		$this->typesOfCaching = getAllColumnsTypesForCaching($this->company->id);
	}

	
	public function cacheSalesBreakdownAnalysisResultForTypes()

	{
		$request = new Request();
		$breakdownCachingForTypes = [];
		// $start_year = $this->year ;
		
		
		// if($this->year != $this->end_year){
			foreach ($this->typesOfCaching as $typeToCache) {
				$request['start_date'] = $this->current_start_date;
				$request['end_date'] = $this->current_end_date;
				$request['type']  = $typeToCache ;
				
				$cacheKeyName = \getBreakdownCacheNameForCompanyAndDatesAndType($this->company,$this->current_start_date,$this->current_end_date, $typeToCache);
				$cacheSimpleLinearRegressionKeyName = \getBreakdownSimpleLinearRegressionCacheNameForCompanyAndDatesAndType($this->company,$this->current_start_date,$this->current_end_date, $typeToCache);
				$cacheSimpleLinearRegressionForCompanyKeyName = \getBreakdownSimpleLinearRegressionCacheNameFor2CompanyAndDatesAndType($this->company,$this->current_start_date,$this->current_end_date, $typeToCache);
				$cacheSimpleLinearRegressionDatesKeyName = \getBreakdownSimpleLinearRegressionDatesCacheNameForCompanyAndDatesAndType($this->company,$this->current_start_date,$this->current_end_date, $typeToCache);
				if (!Cache::has($cacheKeyName)) {
					// $possibleIndexName = '';
					$breakdown_data_with_simple_linear_regression = (new SalesBreakdownAgainstAnalysisReport)->salesBreakdownAnalysisResult($request, $this->company, 'array_with_ai');
					$breakdown_data = $breakdown_data_with_simple_linear_regression['report_view_data'] ?? [];
					$simpleLinearRegression = $breakdown_data_with_simple_linear_regression['simple_linear_regression'] ?? [];
					$aiForCompany = $breakdown_data_with_simple_linear_regression['ai_for_company'] ?? [];
					$simpleLinearRegressionDates = $breakdown_data_with_simple_linear_regression['simple_linear_regression_dates'] ?? [];
					Cache::forever($cacheKeyName, $breakdown_data);
					Cache::forever($cacheSimpleLinearRegressionKeyName, $simpleLinearRegression);
					Cache::forever($cacheSimpleLinearRegressionForCompanyKeyName, $aiForCompany);
					Cache::forever($cacheSimpleLinearRegressionDatesKeyName, $simpleLinearRegressionDates);
				} else {
					$breakdown_data = Cache::get($cacheKeyName);
				}
	
				if (\array_key_exists($typeToCache, $breakdownCachingForTypes)) {
					array_push($breakdownCachingForTypes[$typeToCache], $breakdown_data);
				} else {
					$breakdownCachingForTypes[$typeToCache] = $breakdown_data;
				}
			}
		// } 
		return $breakdownCachingForTypes;
			
			
			
		

	}






	public function cacheAll(): array
	{
		return [
			'breakdownResultForType' => $this->cacheSalesBreakdownAnalysisResultForTypes(),
		];
	}

	public function deleteAll()
	{
		foreach ($this->typesOfCaching as $typeToCache) {
			Cache::forget(\getBreakdownCacheNameForCompanyAndDatesAndType($this->company,$this->current_start_date,$this->current_end_date, $typeToCache));
			Cache::forget(\getBreakdownSimpleLinearRegressionCacheNameForCompanyAndDatesAndType($this->company,$this->current_start_date,$this->current_end_date, $typeToCache));
		}
	}
}
