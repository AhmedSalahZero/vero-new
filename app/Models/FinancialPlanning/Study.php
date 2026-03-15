<?php
namespace App\Models\FinancialPlanning;


use App\Helpers\HHelpers;
use App\Models\FinancialPlanning\Expense;
use App\Models\SalesGathering\Branch;
use App\Models\SalesGathering\Principle;
use App\Models\SalesGathering\Product;
use App\Models\SalesGathering\SalesChannel;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasBasicStoreRequest;
use App\Traits\HasIndexedDates;
use Illuminate\Database\Eloquent\Model;

	/**
 * @property int $id
 * @property string $name اسم الدراسة
 * @property string $company_nature نوع الشركة
 * @property int|null $to_be_consolidated_from_study_id هيكون رقم الدراسة اللي هيختارها
 * @property string $study_start_date
 * @property int $duration_in_years
 * @property string $study_end_date
 * @property float $operation_start_month
 * @property string $operation_start_date
 * @property string $financial_year_start_month
 * @property numeric $corporate_taxes_rate
 * @property numeric $annual_salary_increase_rate
 * @property numeric $salary_taxes_rate
 * @property numeric $social_insurance_rate
 * @property numeric $revenue_multiplier
 * @property numeric $perpetual_growth_rate
 * @property numeric $ebitda_multiplier
 * @property array<array-key, mixed>|null $operation_dates
 * @property array<array-key, mixed>|null $study_dates
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property numeric $shareholder_equity_multiplier
 * @property int $has_trading
 * @property int $has_manufacturing
 * @property int $has_service
 * @property int $has_service_with_inventory
 * @property int $main_planning_base
 * @property int $sub_planning_base
 * @property string|null $add_new_from_main_planning product_or_service , sales_channel , etc
 * @property string|null $add_new_from_sub_planning product_or_service , sales_channel , etc
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesGathering\Branch> $newBranches
 * @property-read int|null $new_branches_count
 * @property-read bool|null $new_branches_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesGathering\Principle> $newPrinciples
 * @property-read int|null $new_principles_count
 * @property-read bool|null $new_principles_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesGathering\Product> $newProducts
 * @property-read int|null $new_products_count
 * @property-read bool|null $new_products_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesGathering\SalesChannel> $newSalesChannels
 * @property-read int|null $new_sales_channels_count
 * @property-read bool|null $new_sales_channels_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereAddNewFromMainPlanning($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereAddNewFromSubPlanning($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereAnnualSalaryIncreaseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereCompanyNature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereCorporateTaxesRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereDurationInYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereEbitdaMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereFinancialYearStartMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereHasManufacturing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereHasService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereHasServiceWithInventory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereHasTrading($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereMainPlanningBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereOperationDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereOperationStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereOperationStartMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study wherePerpetualGrowthRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereRevenueMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereSalaryTaxesRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereShareholderEquityMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereSocialInsuranceRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereStudyDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereStudyEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereStudyStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereSubPlanningBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereToBeConsolidatedFromStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialPlanning\Study whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Study extends Model
	{
		use HasBasicStoreRequest , HasIndexedDates;
		protected  $casts = [
		
			'operation_dates'=>'array',
			'study_dates'=>'array'
		];
		const STUDY = 'study' ;
		// const LEASING ='leasing';
		// const IJARA ='ijara';
		// const DIRECT_FACTORING ='direct-factoring';
		// const REVERSE_FACTORING ='reverse-factoring';
		// const FACTORING_CATEGORY_ID = 'factoring-category-id';
		use CompanyScope,BelongsToCompany;
		
		protected $connection= 'financial_planning';
 	   protected $table = 'studies';

		protected $guarded = [
			'id'
		];
		
		
		
		public static function boot()
		{
			parent::boot();
			static::deleted(function(self $study){
			
			});
		}
		public function getName()
		{
			return $this->name;
		}
		public function getMainFunctionalCurrency()
		{
			return $this->company->getMainFunctionalCurrency();
		}
	// 	public function getPropertyStatus()
	// {
	// 	return $this->property_status;
	// }
	public function getCorporateTaxesRate()
	{
		return $this->corporate_taxes_rate ?: 0;
	}
	public function getAnnualSalaryIncreaseRate()
	{
		return $this->annual_salary_increase_rate ?: 0;
	}
	public function getSalaryTaxesRate()
	{
		return $this->salary_taxes_rate ?: 0 ;
	}
	public function getSocialInsuranceRate()
	{
		return $this->social_insurance_rate ?: 0 ;
	}
	public function getRevenueMultiplier()
	{
		return $this->revenue_multiplier ?: 0 ;
	}
	
	public function getEbitdaMultiplier()
	{
		return $this->ebitda_multiplier ?: 0 ;
	}	
	public function getShareholderEquityMultiplier()
	{
		return $this->shareholder_equity_multiplier ?: 0 ;
	}
	// 	public function getOperationDates(): array
	// {
	// 	return $this->operation_dates ?: [];
	// }
	
	
	public function getCompanyNature()
	{
		return $this->company_nature;
	}		
	
		
	
	public function generateRelationDynamically(string $relationName,string $expenseType){
		/**
		 * * expense type for example CostOfService
		 * * expense 
		 */
		return $this->hasMany(Expense::class , 'model_id','id')->where('model_name','Study')->where('expense_type',$expenseType)->where('relation_name',$relationName);
	}
	
	public  function convertYearToMonthIndexes(array $items):array
	{
		$result = [];

		$operationDurationPerYear=$this->getOperationDurationPerYearFromIndexes();
		foreach($operationDurationPerYear as $yearIndex => $yearMonthIndexes)
			{
				$sumMonths = array_sum($yearMonthIndexes) ;
				foreach($yearMonthIndexes as $monthIndex => $monthlyZeroOrOne ){
					$result[$monthIndex] = $items[$yearIndex]  ;
				}
			}
			return $result;
	}		
	public  function convertYearToMonthIndexesAndDivideBySumMonths(array $items):array
	{
		$result = [];

		$operationDurationPerYear=$this->getOperationDurationPerYearFromIndexes();
		foreach($operationDurationPerYear as $yearIndex => $yearMonthIndexes)
			{
				$sumMonths = array_sum($yearMonthIndexes) ;
				foreach($yearMonthIndexes as $monthIndex => $monthlyZeroOrOne ){
					$result[$monthIndex] = $items[$yearIndex] / $sumMonths ;
				}
			}
			return $result;
	}	
	
	public function getStudyMonthsForView(array $studyMonths , float $durationInYears):array
	{
		$year ='one_year';
		if($durationInYears == 1.5){
			$year ='one_year_and_half';
		}elseif($durationInYears == 2){
			$year ='two_years';
		}elseif($durationInYears == 3){
			$year ='three_years';
		}elseif($durationInYears == 4){
			$year ='four_years';
		}elseif($durationInYears == 5){
			$year ='five_years';
		}
		$indexes = [
			'one_year' => [
				0,1,2,3,4,5,6,7,8,9,10,11
			],
			'one_year_and_half'=>[
				0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17
			],
			'two_years'=>[
				0,1,2,3,4,5,6,7,8,9,10,11,14,17,20,23
			],
			'three_years'=>[
				0,1,2,3,4,5,6,7,8,9,10,11,14,17,20,23,35
			],
			'four_years'=>[
				0,1,2,3,4,5,6,7,8,9,10,11,14,17,20,23,35,47
			],
			'five_years'=>[
				0,1,2,3,4,5,6,7,8,9,10,11,14,17,20,23,35,47,59
			]
			
		][$year] ;
		return collect($studyMonths)->filter(function($studyMonth,$index) use ($indexes){
			return in_array($index,$indexes);
		})->toArray();
	

	}	
			
	public function getToBeConsolidatedFromStudyId()
	{
		return $this->to_be_consolidated_from_study_id;
	}
	public function hasTrading():bool
	{
		return (bool)$this->has_trading;
	}
	public function hasManufacturing():bool
	{
		return (bool)$this->has_manufacturing;
	}
	public function hasService():bool
	{
		return (bool)$this->has_service;
	}
	public function hasServiceWithInventory():bool
	{
		return (bool)$this->has_service_with_inventory;
	}
	public function getMainPlanningBase():?string
	{
		return $this->main_planning_base?:null;
	}
	public function getSubPlanningBase():?string
	{
		return $this->sub_planning_base?:null;
	}
	public function addNewFromMainPlanning():?string 
	{
		return $this->add_new_from_main_planning;
	}
	public function addNewFromSubPlanning():?string 
	{
		return $this->add_new_from_sub_planning;
	}
	public function newProducts()
	{
		return $this->hasMany(Product::class,'study_id','id')->where('is_new',1);
	}
	public function newSalesChannels()
	{
		return $this->hasMany(SalesChannel::class,'study_id','id')->where('is_new',1);
	}
	public function newBranches()
	{
		return $this->hasMany(Branch::class,'study_id','id')->where('is_new',1);
	}
	public function newPrinciples()
	{
		return $this->hasMany(Principle::class,'study_id','id')->where('is_new',1);
	}
	public static function getRelationName():array 
	{
		return [
			'product_or_service'=>'newProducts',
			'sales_channel'=>'newSalesChannels',
			'branch'=>'newBranches',
			'principle'=>'newPrinciples'
		];
	}
	/* 
	* * type -> manpower for example 
	* * expense_type -> cost-of-service for example
	 */
	// public function departmentsFor(string $type , string $expenseType)
	// {
	// 	return Department::where('study_id',$this->id)->where('expense_type',$expenseType)->where('type',$type)->get();
	// }
	public function financialYearStartMonth(): ?string
	{
		return $this->financial_year_start_month;
	}
	 public function getFinancialYearEndMonthNumber():int
	 {
		$financialYearStartMonthName = $this->financialYearStartMonth();
		if($financialYearStartMonthName =='january')
			return 12;
		if($financialYearStartMonthName =='april')
			return 3;
		if($financialYearStartMonthName =='july')
			return 6;
		return 12;
	 }
	 public function getStudyDurationPerYearFromIndexesForView() 
	 {
		$datesAsStringAndIndex = $this->getDatesAsStringAndIndex();
		$datesIndexWithYearIndex = App('datesIndexWithYearIndex');
		$yearIndexWithYear = App('yearIndexWithYear');
		$dateIndexWithDate = App('dateIndexWithDate');
		$dateWithMonthNumber = App('dateWithMonthNumber');
		return $this->getStudyDurationPerMonth($datesAsStringAndIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate,$dateWithMonthNumber,true,false);
	}

	
		
		
}
