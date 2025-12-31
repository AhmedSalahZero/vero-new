<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\NonBankingService\Study;
use Illuminate\Database\Eloquent\Model;



class  PortfolioMortgageRevenueStreamBreakdown extends Model
{

	public function getViewVars(Company $company, Study $study):array{
//		$portfolioMortgageEclAndNewPortfolioFundingRate = $study?  $study->portfolioMortgageEclAndNewPortfolioFundingRate : null;
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		$isYearsStudy = !$study->isMonthlyStudy();
		
		return [
			'eclAndNewPortfolioFundingRate'=>$study->getEclAndNewPortfolioFundingRatesForStreamType(Study::PORTFOLIO_MORTGAGE),
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
	//		'portfolioMortgageEclAndNewPortfolioFundingRate'=>$portfolioMortgageEclAndNewPortfolioFundingRate,
			'title'=>__('Portfolio Mortgage Revenue Stream Breakdown'),
			'storeRoute'=>routeWithQueryParam(route('store.portfolio.mortgage.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			'yearsWithItsMonths' => $yearsWithItsMonths,
			'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
			'isYearsStudy'=>$isYearsStudy
			
		];
	}
	public function getFormName():string
	{
		return 'non_banking_services.portfolio-mortgage-revenue-stream-breakdown.form';
	}
}
