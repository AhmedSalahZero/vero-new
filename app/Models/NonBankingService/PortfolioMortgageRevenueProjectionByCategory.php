<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property numeric $monthly_margin_rate
 * @property numeric $quarterly_margin_rate
 * @property numeric $annually_margin_rate
 * @property array<array-key, mixed>|null $monthly_due_cheques_percentages
 * @property array<array-key, mixed>|null $quarterly_due_cheques_percentages
 * @property array<array-key, mixed>|null $annually_due_cheques_percentages
 * @property array<array-key, mixed>|null $growth_rates
 * @property array<array-key, mixed>|null $portfolio_mortgage_transactions_projections
 * @property array<array-key, mixed>|null $frequency_per_year
 * @property array<array-key, mixed>|null $start_from
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property numeric $margin_rate
 * @property int $portfolio_mortgage_duration
 * @property string|null $occurrence_dates
 * @property string|null $statement
 * @property string|null $loan_amounts
 * @property array<array-key, mixed>|null $total_monthly_amounts_per_years
 * @property string|null $portfolio_mortgage_unearned_interest_statement
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereAnnuallyDueChequesPercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereAnnuallyMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereFrequencyPerYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereGrowthRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereMonthlyDueChequesPercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereMonthlyMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereOccurrenceDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory wherePortfolioMortgageDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory wherePortfolioMortgageTransactionsProjections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory wherePortfolioMortgageUnearnedInterestStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereQuarterlyDueChequesPercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereQuarterlyMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereStartFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereTotalMonthlyAmountsPerYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\PortfolioMortgageRevenueProjectionByCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PortfolioMortgageRevenueProjectionByCategory extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';

	protected $guarded = ['id'];
	protected $casts =[
		'growth_rates'=>'array',
		'portfolio_mortgage_transactions_projections'=>'array',
		'frequency_per_year'=>'array',
		'start_from'=>'array',
		'monthly_due_cheques_percentages'=>'array',
		'quarterly_due_cheques_percentages'=>'array',
		'annually_due_cheques_percentages'=>'array',
		'total_monthly_amounts_per_years'=>'array', // we not longer need this column
	];
	public function getViewVars(Company $company, Study $study):array{
	//	$portfolioMortgageEclAndNewPortfolioFundingRate = $study?  $study->portfolioMortgageEclAndNewPortfolioFundingRate : null;
		return [
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
	//		'portfolioMortgageEclAndNewPortfolioFundingRate'=>$portfolioMortgageEclAndNewPortfolioFundingRate,
			'title'=>__('Portfolio Mortgage Revenue Stream Breakdown'),
			'storeRoute'=>route('store.portfolio.mortgage.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id]),
			'yearsWithItsMonths' => $study->getOperationDurationPerYearFromIndexes(),
		];
	}
	public function getFormName():string
	{
		return 'non_banking_services.portfolio-mortgage-revenue-stream-breakdown.form';
	}
	
	
	public function getPortfolioMortgageTransactionProjection():array 
	{
		return (array)$this->portfolio_mortgage_transactions_projections  ; 
	}
	public function getPortfolioMortgageTransactionProjectionAtYearOrMonthIndexIndex(int $yearOrMonthIndex)
	{
		return $this->getPortfolioMortgageTransactionProjection()[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getStartFrom():array 
	{
		return (array)$this->start_from  ; 
	}
	public function getStartFromAtYearIndex(int $yearIndex)
	{
		return $this->getStartFrom()[$yearIndex] ?? 0  ; 
	}
	public function getFrequencyPerYear():array 
	{
		return (array)$this->frequency_per_year  ; 
	}
	public function getFrequencyPerYearAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getFrequencyPerYear()[$yearOrMonthIndex] ?? 0  ; 
	}
 	public function getGrowthRateAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->growth_rates[$yearOrMonthIndex] ?? 0  ; 
	}

	// public function getMonthlyMarginRate()
	// {
	// 	return $this->monthly_margin_rate ?: 0;
	// }
	// public function getQuarterlyMarginRate()
	// {
	// 	return $this->quarterly_margin_rate ?: 0;
	// }
	// public function getAnnuallyMarginRate()
	// {
	// 	return $this->annually_margin_rate ?: 0;
	// }
	// public function getMonthlyDueChequesPercentagesAtYearIndex(int $yearIndex)
	// {
	// 	return $this->monthly_due_cheques_percentages[$yearIndex] ?? 0  ; 
	// }
	// public function getQuarterlyDueChequesPercentagesAtYearIndex(int $yearIndex)
	// {
	// 	return $this->quarterly_due_cheques_percentages[$yearIndex] ?? 0  ; 
	// }
	// public function getAnnuallyDueChequesPercentagesAtYearIndex(int $yearIndex)
	// {
	// 	return $this->annually_due_cheques_percentages[$yearIndex] ?? 0  ; 
	// }	
	public function getMarginRate()
	{
		$marginRate = $this->margin_rate;
		return $marginRate ? $this->margin_rate : 0 ;
	}
	public function getMortgageDuration()
	{
		return $this->portfolio_mortgage_duration;
	}
	public function getForeignKeyName():string
	{
		return 'portfolio_mortgage_category_id';
	}	
	public function getCategoryColumnName():?string 
	{
			$idAndTitleColumnNames = Study::getRevenueStreamCategoryColumnsFor('portfolioMortgageRevenueProjectionByCategories');
		$id = $idAndTitleColumnNames['id'];
		return $id;
	}
	public function getCategoryId()
	{
		$id = $this->getCategoryColumnName();
		return $this->{$id};
		
	}
	public function getRevenueType():string 
	{
		return Study::PORTFOLIO_MORTGAGE;
	}
	// public function getCategoryColumnName():string 
	// {
	// 	return '';
	// }
	public function getPortfolioMortgageTransactionProjects(array $datesAsIndexes)
	{
		$result = [];
		foreach($datesAsIndexes as $dateAsIndex){
			$result[$dateAsIndex] =$this->getPortfolioMortgageTransactionProjectionAtYearOrMonthIndexIndex($dateAsIndex); 
		}
		return $result;
	}
	public static function  getRow(?self $portfolioMortgage,array $datesAsIndexes,array $categories)
	{
		$duration = $portfolioMortgage? $portfolioMortgage->portfolio_mortgage_duration : $categories[0]['id'];
		return [
			'portfolio_mortgage_duration'=>(float)$duration  , // first one is the default one
			'margin_rate'=>$portfolioMortgage ? $portfolioMortgage->getMarginRate()  : 0 ,
			'portfolio_mortgage_transactions_projections'=>$portfolioMortgage ? $portfolioMortgage->getPortfolioMortgageTransactionProjects($datesAsIndexes) : array_fill_keys($datesAsIndexes,0),
		];
	}
	
}
