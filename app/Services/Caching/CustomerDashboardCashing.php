<?php

namespace App\Services\Caching;

use App\Models\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomerDashboardCashing
{
	private Company $company;
	private string $year;
	private string $month;
	private string $newCustomerCashingName;
	private string $repeatingCustomerCashingName;
	private string $activeCustomerCashingName;
	private string $stopReactivatedCustomerCashingName;
	private string $deadReactivatedCustomerCashingName;
	private string $stopRepeatingCustomerCashingName;
	private string $deadRepeatingCustomerCashingName;
	private string $stopCustomerCashingName;
	private string $deadCustomerCashingName;
	private string $totalCustomerCashingName;
	private array $typesOfCaching;

	public function __construct(Company $company, string $year , string $month)
	{
		$this->company = $company;
		$this->year = $year;
		$this->month = $month;
		$this->newCustomerCashingName  = getNewCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->repeatingCustomerCashingName = getRepeatingCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->activeCustomerCashingName = getActiveCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->stopReactivatedCustomerCashingName = getStopReactivatedCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->deadReactivatedCustomerCashingName = getDeadReactivatedCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->stopRepeatingCustomerCashingName = getStopRepeatingCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->deadRepeatingCustomerCashingName = getDeadRepeatingCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->stopCustomerCashingName = getStopCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->deadCustomerCashingName = getDeadCustomersCacheNameForCompanyInYear($company, $year,$month);
		$this->totalCustomerCashingName = getTotalCustomersCacheNameForCompanyInYear($this->company, $this->year,$month);
	}
	////////dddd
	public function cacheNewCustomers()

	{

		if (!Cache::has($this->newCustomerCashingName)) {

			$query = "
                select  customer_name , min(Year) as first_appearnce , min(Year) -1 as previous_appearance , count(*) as no_customers,
                 sum(case when Year = " . $this->year  . " and Month <= ". $this->month ." then net_sales_value else 0 end ) total_sales  from sales_gathering 
                 force index (min__index)
                  where company_id = " . $this->company->id . " group by customer_name having  min(Year) = " . $this->year   . " order by total_sales desc
                ";
			$newCustomers = DB::select($query);
			Cache::forever($this->newCustomerCashingName, $newCustomers);
		} else {
			$newCustomers = Cache::get($this->newCustomerCashingName);
		}

		return $newCustomers;
	}
	
	public function cacheTotalCustomers()
	{

		if (!Cache::has($this->totalCustomerCashingName)) {
			$query = "
                select customer_name ,
             sum(net_sales_value) as val , count(*) as no_customers,
              FORMAT((sum(net_sales_value) / (select sum(net_sales_value)  from sales_gathering force index (min__index) where company_id
               = " . $this->company->id . "  and month <= ". $this->month ."  and Year = " . $this->year . " ) * 100) , 1) as percentage
                from sales_gathering force index (min__index) where company_id = " . $this->company->id  . " and month <= ". $this->month ." and Year = " . $this->year . "
                group by customer_name 
                order by val desc ";
			$totals = DB::select($query);


			Cache::forever($this->totalCustomerCashingName, $totals);
		} else {
			$totals = Cache::get($this->totalCustomerCashingName);
		}
		return $totals;
	}
	
	function formatDataForType(array $dataOfArray, string  $typeToCache)
	{
		$formattedData = [];

		foreach ($dataOfArray as $index => $dataObj) {
			$groupingKey  = $dataObj->{$typeToCache};

			isset($formattedData[$groupingKey]) ?  array_push($formattedData[$groupingKey], $dataObj) : $formattedData[$groupingKey] = [$dataObj];
		}
		return $formattedData;
	}

	public function cacheRepeatingCustomers()
	{
		if (!Cache::has($this->repeatingCustomerCashingName)) {


			$query = "
        select  customer_name , min(Year) as date ,count(*) as no_customers, sum(case when Year = " . $this->year . " and Month <= ". $this->month ." then net_sales_value else 0 end ) total_sales  from sales_gathering
         force index (min__index)
        
        where company_id = " . $this->company->id . " group by customer_name having  min(Year) = " . ($this->year - 1) . " and 
        max(case when Year = " . $this->year . " then 1 else 0 end ) = 1 order by total_sales desc
        ";
			$RepeatingCustomers = DB::select($query);


			Cache::forever($this->repeatingCustomerCashingName, $RepeatingCustomers);
		} else {
			$RepeatingCustomers = Cache::get(getRepeatingCustomersCacheNameForCompanyInYear($this->company, $this->year,$this->month));
		}



		return $RepeatingCustomers;
	}




	public function cacheActiveCustomers()
	{
		if (!Cache::has($this->activeCustomerCashingName)) {
			$query = "
                select (customer_name) ,count(*) as no_customers, sum(case when Year = " . $this->year . " and Month  <= ". $this->month ." then net_sales_value else 0 end ) total_sales
                from sales_gathering 
                force index (min__index)
                where company_id = " . $this->company->id . "
                GROUP by customer_name
                having max(case when Year = " . $this->year . " then 1 else 0 end ) = 1 
                and max(case when  Year = " . ($this->year - 1) . " then 1 else 0 end ) = 1 
                and max(case when Year = " . ($this->year - 2) . " then 1 else 0 end ) = 1
                ORDER BY total_sales DESC
                ";
			$activeCustomers = DB::select($query);


			Cache::forever($this->activeCustomerCashingName, $activeCustomers);
		} else {
			$activeCustomers = Cache::get($this->activeCustomerCashingName);
		}

		return $activeCustomers;
	}






	public function cacheStopReactivatedCustomers()
	{
		if (!Cache::has($this->stopReactivatedCustomerCashingName)) {
			$query = "
                    select (customer_name) ,count(*) as no_customers, sum(case when Year = " . $this->year . " and Month <= ".  $this->month  ." then net_sales_value else 0 end ) total_sales from sales_gathering 
                     force index (min__index)
                    where company_id = " . $this->company->id . "
                    GROUP by customer_name
                    having max(case when Year = " . ($this->year) . " then 1 else 0 end ) = 1
                    and max(case when Year = " . ($this->year - 1) . " then 1 else 0 end ) = 0
                    and max(case when Year = " . ($this->year - 2) . " then 1 else 0 end ) = 1
                    order by total_sales desc 
                    ";
			$stopReactive = DB::select($query);

			Cache::forever($this->stopReactivatedCustomerCashingName, $stopReactive);
		} else {
			$stopReactive = Cache::get($this->stopReactivatedCustomerCashingName);
		}
		return $stopReactive;
	}


	public function cacheDeadReactivatedCustomers()
	{
		if (!Cache::has($this->deadReactivatedCustomerCashingName)) {
			$havingCondition = getHavingConditionForDeadReactivated($this->year);
			$query = "
                    select (customer_name) ,count(*) as no_customers, sum(case when year = " .  $this->year   . " and Month <= ". $this->month ." then net_sales_value else 0 end ) total_sales
                    from sales_gathering force index (min__index)
                    where company_id = " . $this->company->id  . "
                    GROUP by customer_name " . $havingCondition;
			$deadReactivatedCustomers = DB::select($query);

			Cache::forever($this->deadReactivatedCustomerCashingName, $deadReactivatedCustomers);
		} else {
			$deadReactivatedCustomers = Cache::get($this->deadReactivatedCustomerCashingName);
		}
		return $deadReactivatedCustomers;
	}



	public function cacheStopRepeatingCustomers()
	{
		if (!Cache::has($this->stopRepeatingCustomerCashingName)) {
			$query = "
                select (customer_name) , count(*) as no_customers,sum(case when year = " .  $this->year   . " and Month <= ". $this->month ." then net_sales_value else 0 end) total_sales
                from sales_gathering force index (min__index)
                where company_id = " . $this->company->id  . "
                GROUP by customer_name
                having max(case when Year = " . $this->year . " then 1 else 0 end ) = 1
                and max(case when Year = " . ($this->year - 1) . " then 1 else 0 end ) = 1 
                and max(case when Year = " . ($this->year - 2)  . " then 1 else 0 end ) = 0 
                and max(case when Year = " . ($this->year - 3)  . " then 1 else 0 end ) = 1 
                order by total_sales desc
                ";
			$stopRepeatingCustomers = DB::select($query);

			Cache::forever($this->stopRepeatingCustomerCashingName, $stopRepeatingCustomers);
		} else {
			$stopRepeatingCustomers = Cache::get($this->stopRepeatingCustomerCashingName);
		}

		return $stopRepeatingCustomers;
	}



	public function cacheDeadRepeatingCustomers()
	{
		if (!Cache::has($this->deadRepeatingCustomerCashingName)) {
			$havingCondition = getHavingConditionForDeadRepeating($this->year);

			$query = "select (customer_name) ,count(*) as no_customers, sum(case when year = " .  $this->year   . " and Month <= ". $this->month ." then net_sales_value else 0 end ) total_sales
                    from sales_gathering force index (min__index)
                    where company_id = " . $this->company->id  . "
                    GROUP by customer_name " . $havingCondition;
			$deadRepeatingCustomers = DB::select($query);

			Cache::forever($this->deadRepeatingCustomerCashingName, $deadRepeatingCustomers);
		} else {
			$deadRepeatingCustomers = Cache::get($this->deadRepeatingCustomerCashingName);
		}
		return $deadRepeatingCustomers;
	}




	public function cacheStopCustomers()
	{

		if (!Cache::has($this->stopCustomerCashingName)) {
			$query = "
            select (customer_name) , count(*) as no_customers, sum(case when year = " .  ($this->year - 1)   . " and Month <= ". $this->month ." then net_sales_value else 0 end) total_sales
            from  sales_gathering force index (min__index)
            where company_id = " . $this->company->id  . "
            GROUP by customer_name
            having max(case when Year = " . $this->year .  " then 1 else 0 end ) = 0
            and max(case when Year = " . ($this->year - 1) . " then 1 else 0 end ) = 1 
            order by total_sales desc
            ";
			$stopCustomers = DB::select($query);

			Cache::forever($this->stopCustomerCashingName, $stopCustomers);
		} else {
			$stopCustomers = Cache::get($this->stopCustomerCashingName);
		}
		return $stopCustomers;
	}



	public function cacheDeadCustomers()
	{

		if (!Cache::has($this->deadCustomerCashingName)) {


			$query = "
                
                select (customer_name) , count(*) as no_customers, sum(case when Year = " . ($this->year - 2)  . " and Month <= ". $this->month ." then net_sales_value else 0 end ) total_sales
                from sales_gathering force index (min__index)
                where company_id = " . $this->company->id . "
                
                GROUP by customer_name
                having max(case when Year = " . $this->year  . " then 1 else 0 end ) = 0
                and max(case when Year = " . ($this->year - 1) . " then 1 else 0 end ) = 0 
                and max(case when Year = " . ($this->year - 2) . " then 1 else 0 end ) = 1 
                order by total_sales desc;
                
                ";
			$deadCustomers = DB::select($query);



			Cache::forever($this->deadCustomerCashingName, $deadCustomers);
		} else {
			$deadCustomers = Cache::get($this->deadCustomerCashingName);
		}

		return $deadCustomers;
	}





	





	public function cacheAll(): array
	{
		return [
			'newCustomers' => $this->cacheNewCustomers(),
			'RepeatingCustomers' => $this->cacheRepeatingCustomers(),
			'activeCustomers' => $this->cacheActiveCustomers(),
			'stopCustomers' => $this->cacheStopCustomers(),
			'stopReactive' => $this->cacheStopReactivatedCustomers(),
			'deadReactivatedCustomers' => $this->cacheDeadReactivatedCustomers(),
			'stopRepeatingCustomers' => $this->cacheStopRepeatingCustomers(),
			'deadRepeatingCustomers' => $this->cacheDeadRepeatingCustomers(),
			'deadCustomers' => $this->cacheDeadCustomers(),
			'totals' => $this->cacheTotalCustomers(),

		];
	}

	public function deleteAll()
	{
		Cache::forget($this->newCustomerCashingName);
		Cache::forget($this->repeatingCustomerCashingName);
		Cache::forget($this->activeCustomerCashingName);
		Cache::forget($this->stopCustomerCashingName);
		Cache::forget($this->stopReactivatedCustomerCashingName);
		Cache::forget($this->deadReactivatedCustomerCashingName);
		Cache::forget($this->stopRepeatingCustomerCashingName);
		Cache::forget($this->deadRepeatingCustomerCashingName);
		Cache::forget($this->deadCustomerCashingName);
		Cache::forget($this->totalCustomerCashingName);
	}
}
