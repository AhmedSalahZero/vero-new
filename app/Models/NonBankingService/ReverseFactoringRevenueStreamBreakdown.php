<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\LoanScheduleSettlement;
use App\Models\NonBankingService\Study;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\IsRevenueStream;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;



class  ReverseFactoringRevenueStreamBreakdown extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy , IsRevenueStream;
	protected $connection= 'non_banking_service';
	protected $table = 'reverse_factoring_breakdowns';
	protected $guarded = ['id'];
	protected $casts =[
		'loan_amounts'=>'array',
		// 'growth_rate'=>'array'
	];
	// public function loanSchedulePayments()
	// {
	// 	return $this->hasMany(LoanScheduleSettlement::class,'revenue_stream_id','id');
	// }
	public static function boot()
		{
			parent::boot();
			static::deleted(function(self $reverseRevenueStreamBreakdown){
				// $reverseRevenueStreamBreakdown->loanSchedulePayments->each(function(LoanScheduleSettlement $loanScheduleSettlement) {
				// 	$loanScheduleSettlement->delete();
				// });
			});
		}
	// public function category()
	// {
	// 	return $this->belongsTo(ReverseCategory::class,'category_id',) ;
	// }
	
	
	public function getLoanAmountAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->loan_amounts[$yearOrMonthIndex] ?? 0  ; 
	}
	
	public function getViewVars(Company $company, Study $study):array{
		$reverseFactoringEclAndNewPortfolioFundingRate = $study?  $study->reverseFactoringEclAndNewPortfolioFundingRate : null;
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		$isYearsStudy = !$study->isMonthlyStudy();
		
		return [
			'eclAndNewPortfolioFundingRate'=>$study->getEclAndNewPortfolioFundingRatesForStreamType(Study::REVERSE_FACTORING),
			'company'=>$company ,
			'study'=>$study,
			'model'=>$study ,
			'reverseFactoringEclAndNewPortfolioFundingRate'=>$reverseFactoringEclAndNewPortfolioFundingRate,
			'title'=>__('Reverse Factoring Revenue Stream Breakdown'),
			'storeRoute'=>routeWithQueryParam(route('store.reverse.factoring.revenue.stream.breakdown',['company'=>$company->id , 'study'=>$study->id])),
			'yearsWithItsMonths' => $yearsWithItsMonths,
			'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
			'isYearsStudy'=>$isYearsStudy
		];
	}
	public function getFormName():string
	{
		return 'non_banking_services.reverse-factoring-revenue-stream-breakdown.form';
	}
	

	
}
