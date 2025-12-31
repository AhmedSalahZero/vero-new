<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  PortfolioMortgageRevenueProjectionByCategory extends Model
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
		'total_monthly_amounts_per_years'=>'array',
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
	
}
