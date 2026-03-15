<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $growth_rates
 * @property array<array-key, mixed>|null $reverse_factoring_transactions_projections
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory whereGrowthRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory whereReverseFactoringTransactionsProjections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReverseFactoringRevenueProjectionByCategory extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';

	protected $guarded = ['id'];
	protected $casts =[
		'growth_rates'=>'array',
		'reverse_factoring_transactions_projections'=>'array',
	];
	public function getViewVars(Company $company, Study $study):array{
		$reverseFactoringEclAndNewPortfolioFundingRate =  $study->reverseFactoringEclAndNewPortfolioFundingRate ;
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
