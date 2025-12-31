<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  IjaraMortgageRevenueProjectionByCategory extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';

	protected $guarded = ['id'];
	protected $casts =[
		'growth_rates'=>'array',
		'monthly_due_cheques_percentages'=>'array',
		'ijara_mortgage_transactions_projections'=>'array',
	];
	public function getViewVars(Company $company, Study $study):array{
		$ijaraMortgageEclAndNewPortfolioFundingRate = $study?  $study->ijaraMortgageEclAndNewPortfolioFundingRate : null;
		return [
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
			'ijaraMortgageEclAndNewPortfolioFundingRate'=>$ijaraMortgageEclAndNewPortfolioFundingRate,
			'title'=>__('Ijara Mortgage Revenue Stream Breakdown'),
			'storeRoute'=>route('store.ijara.mortgage.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id]),
			'yearsWithItsMonths' => $study->getOperationDurationPerYearFromIndexes(),
		];
	}
	public function getFormName():string
	{
		return 'non_banking_services.ijara-mortgage-revenue-stream-breakdown.form';
	}
	
	public function getIjaraMortgageTransactionProjectionAtYearIndex(int $yearIndex)
	{
		return $this->getIjaraMortgageTransactionProjection()[$yearIndex] ?? 0  ; 
	}
	public function getIjaraMortgageTransactionProjection():array 
	{
		return $this->ijara_mortgage_transactions_projections;
	}
	public function getGrowthRateAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->growth_rates[$yearOrMonthIndex] ?? 0  ; 
	}	
	
	
		
}
