<?php
namespace App\Services\Caching;

use App\Models\Company;
use App\Services\Caching\BreakdownCashing;
use App\Services\Caching\CustomerDashboardCashing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CashingService
{
    private Company $company ; 
    
    public function __construct(Company $company )
    {
            $this->company = $company ; 
    }
    
    public function getIntervalYearsFormCompany():array
    {
        $IntervalYearsFormCompanyCacheNameForCompany = getIntervalYearsFormCompanyCacheNameForCompany($this->company);
        if(Cache::has($IntervalYearsFormCompanyCacheNameForCompany))
        {
             $years = Cache::get($IntervalYearsFormCompanyCacheNameForCompany) ;
        }
        else{
            $years  = DB::select(DB::raw(
            "select min(date_format(date , '%Y')) start_date ,max(date_format(date , '%Y')) end_date , max(date) full_end_date from sales_gathering  where company_id = " . $this->company->id 
        )) ;
		
        Cache::forever($IntervalYearsFormCompanyCacheNameForCompany , $years);

        }
		$elements = [
            'start_year'=>$years[0]->start_date,
            'end_year'=>$years[0]->end_date,
			'full_date'=>$years[0]->full_end_date
        ];
		if(isset($years[0]->full_end_date)){
			$elements['full_end_date'] =$years[0]->full_end_date ; 
		}
		
        return  $elements  ; 
    }
    
	public function getExpenseIntervalYearsFormCompany():array
    {
        $IntervalYearsFormCompanyCacheNameForCompany = getExpenseIntervalYearsFormCompanyCacheNameForCompany($this->company);
        if(Cache::has($IntervalYearsFormCompanyCacheNameForCompany))
        {
             $years = Cache::get($IntervalYearsFormCompanyCacheNameForCompany) ;
        }

        else{
            $years  = DB::select(DB::raw(
            "select min(date_format(date , '%Y')) start_date ,max(date_format(date , '%Y')) end_date , max(date) full_end_date from expense_analysis  where company_id = " . $this->company->id 
        )) ;
		
        Cache::forever($IntervalYearsFormCompanyCacheNameForCompany , $years);

        }
		$elements = [
            'start_year'=>$years[0]->start_date,
            'end_year'=>$years[0]->end_date,
        ];
		if(isset($years[0]->full_end_date)){
			$elements['full_end_date'] =$years[0]->full_end_date ; 
		}
		
        return  $elements  ; 
    }
	
    public function cacheAll()
    {
             $years = $this->getIntervalYearsFormCompany(); 
            $startYear = $years['start_year'] ; 
            $endYear = $years['end_year'] ; 
            $fullData = $years['full_date'] ; 
			$date = Carbon::make($fullData)->format('Y-m-d');
			$month  = explode('-',$date)[1];
			
            if($startYear && $endYear){
                for($year = $startYear ; $year <= $endYear ; $year++)
                {
                        (new CustomerDashboardCashing($this->company , $year,$month))->cacheAll();
                        (new CustomerNatureCashing($this->company , $year,$month))->cacheAll();
                        (new BreakdownCashing($this->company , $year,$endYear))->cacheAll();
                }
            }
    }
    public function removeAll()
    {
            // add the following code in class for generic items
            Cache::forget(getIntervalYearsFormCompanyCacheNameForCompany($this->company));
            
            $years = $this->getIntervalYearsFormCompany(); 
            
			$fullData = $years['full_date'] ; 
			$date = Carbon::make($fullData)->format('Y-m-d');
			$month  = explode('-',$date)[1];
			
            if($years['start_year'] && $years['end_year']){
                for($year = $years['start_year'] ; $year <= $years['end_year'] ; $year++)
                {
                        (new CustomerDashboardCashing($this->company , $year,$month))->deleteAll();
                        (new CustomerNatureCashing($this->company , $year,$month))->deleteAll();
                        (new BreakdownCashing($this->company , $year,$years['end_year']))->deleteAll();
                }
            }
    }

    public function removeIntervalYearsCaching()
    {
	
        Cache::forget(getIntervalYearsFormCompanyCacheNameForCompany($this->company));
    }
	public function removeExpenseIntervalYearsCaching()
    {
        Cache::forget(getExpenseIntervalYearsFormCompanyCacheNameForCompany($this->company));
    }
    public function refreshCustomerDashboardCashing()
    {
        // remove then reAdd 
          // add the following code in class for generic items
        
        $years = $this->getIntervalYearsFormCompany(); 
        $exportables = getExportableFields($this->company->id);
         
            $startYear = $years['start_year'] ; 
            $endYear = $years['end_year'] ; 
            $fullData = $years['full_date'] ; 
			if(is_null($fullData)){
				return ;
			}
			$date = Carbon::make($fullData)->format('Y-m-d');
			$month  = explode('-',$date)[1];
            if($startYear && $endYear){
                for($year = $startYear ; $year <= $endYear ; $year++)
                {
                    // 1- customer dashboard 
                  
                    if(canViewCustomersDashboard($exportables)){
                        $customerDashboardCashing = new CustomerDashboardCashing($this->company , $year,$month); 
                        $customerDashboardCashing->deleteAll();
                        $customerDashboardCashing->cacheAll();   
                    }
                }
            }
        
    }


    public function refreshCustomerNatureCashing()
    {
        // remove then reAdd 
          // add the following code in class for generic items
        
        $years = $this->getIntervalYearsFormCompany(); 
        $exportables = getExportableFields($this->company->id);
         
            $startYear = $years['start_year'] ; 
            $endYear = $years['end_year'] ; 
            $fullData = $years['full_date'] ; 
			
            $date = Carbon::make($fullData)->format('Y-m-d');
			$month  = explode('-',$date)[1];
			
            if($startYear && $endYear){
                for($year = $startYear ; $year <= $endYear ; $year++)
                {
                    // 1- customer dashboard 
                  
                    if(canViewCustomersDashboard($exportables)){
                    
                        $customerNatureCashing = new CustomerNatureCashing($this->company , $year,$month); 
                        $customerNatureCashing->deleteAll();
                        $customerNatureCashing->cacheAll();   
                    }
                }
            }
        
    }
	
	public function refreshBreakdownDashboardCashing()
    {
        // remove then reAdd 
          // add the following code in class for generic items
        
        $years = $this->getIntervalYearsFormCompany(); 
        $exportables = getExportableFields($this->company->id);
         
            $startYear = $years['start_year'] ; 
            $endYear = $years['end_year'] ; 
            
            if($startYear && $endYear){
                for($year = $startYear ; $year <= $endYear ; $year++)
                {
                        $breakdownDashboardCashing = (new BreakdownCashing($this->company , $year,$endYear)); 
                        $breakdownDashboardCashing->deleteAll();
                        $breakdownDashboardCashing->cacheAll();   
                    
                }
            }
        
    }
    




}
