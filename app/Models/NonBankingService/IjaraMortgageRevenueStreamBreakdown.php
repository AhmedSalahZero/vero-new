<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\NonBankingService\Study;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueStreamBreakdown newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueStreamBreakdown newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueStreamBreakdown query()
 * @mixin \Eloquent
 */
class IjaraMortgageRevenueStreamBreakdown extends Model
{

	public function getViewVars(Company $company, Study $study):array{
		// $ijaraMortgageEclAndNewPortfolioFundingRate = $study?  $study->ijaraMortgageEclAndNewPortfolioFundingRate : null;
		// $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		// $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		// $isYearsStudy = !$study->isMonthlyStudy();
		return [
			// 'eclAndNewPortfolioFundingRate'=>$study->getEclAndNewPortfolioFundingRatesForStreamType(Study::IJARA),
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
			'title'=>__('Ijara Mortgage Revenue Stream Breakdown'),
			// 'ijaraMortgageEclAndNewPortfolioFundingRate'=>$ijaraMortgageEclAndNewPortfolioFundingRate,
			// 'storeRoute'=>routeWithQueryParam(route('store.ijara.mortgage.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			// 'yearsWithItsMonths' => $yearsWithItsMonths,
			// 'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
			// 'isYearsStudy'=>$isYearsStudy
		];
	}
	public function getFormName():string
	{
		return 'non_banking_services.ijara-mortgage-revenue-stream-breakdown.form';
	}
}
