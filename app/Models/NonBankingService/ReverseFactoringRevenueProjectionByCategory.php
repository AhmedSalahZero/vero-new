<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  ReverseFactoringRevenueProjectionByCategory extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';

	protected $guarded = ['id'];
	protected $casts =[
		'growth_rates'=>'array',
		'reverse_factoring_transactions_projections'=>'array',
	];
	public function getViewVars(Company $company, Study $study):array{
		$reverseFactoringEclAndNewPortfolioFundingRate = $study?  $study->reverseFactoringEclAndNewPortfolioFundingRate : null;
		return [
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
			'reverseFactoringEclAndNewPortfolioFundingRate'=>$reverseFactoringEclAndNewPortfolioFundingRate,
			'title'=>__('Reverse Factoring Revenue Stream Breakdown'),
			'storeRoute'=>route('store.reverse.factoring.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id]),
			'yearsWithItsMonths' => $study->getOperationDurationPerYearFromIndexes(),
		];
	}
	public function getFormName():string
	{
		return 'non_banking_services.reverse-factoring-revenue-stream-breakdown.form';
	}
	
	public function getReverseFactoringTransactionProjectionAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->getReverseFactoringTransactionProjection()[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getReverseFactoringTransactionProjection()
	{
		return $this->reverse_factoring_transactions_projections;
	}
	public function getGrowthRateAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->growth_rates[$yearOrMonthIndex] ?? 0  ; 
	}
	
		
}
