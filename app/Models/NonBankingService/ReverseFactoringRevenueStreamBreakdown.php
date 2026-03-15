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



/**
 * @property int $id
 * @property string|null $category
 * @property numeric $margin_rate
 * @property numeric $sensitivity_margin_rate
 * @property float $tenor
 * @property string|null $percentage_payload
 * @property array<array-key, mixed>|null $loan_amounts
 * @property string|null $monthly_loan_amounts
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereMonthlyLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown wherePercentagePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereSensitivityMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereTenor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringRevenueStreamBreakdown whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReverseFactoringRevenueStreamBreakdown extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
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
		$reverseFactoringEclAndNewPortfolioFundingRate =  $study->reverseFactoringEclAndNewPortfolioFundingRate ;
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
