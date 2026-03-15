<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\IsRevenueStream;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $study_id
 * @property int $company_id
 * @property string|null $category_id
 * @property array<array-key, mixed>|null $monthly_loan_amounts
 * @property array<array-key, mixed>|null $contract_counts
 * @property string|null $revenue_type
 * @property int|null $leasing_breakdown_id
 * @property int|null $ijara_breakdown_id
 * @property int|null $reverse_breakdown_id
 * @property int|null $direct_breakdown_id
 * @property int $portfolio_mortgage_category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\LeasingCategory|null $category
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereContractCounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereDirectBreakdownId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereIjaraBreakdownId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereLeasingBreakdownId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereMonthlyLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract wherePortfolioMortgageCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereRevenueType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereReverseBreakdownId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RevenueContract whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RevenueContract extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy 
	// , IsRevenueStream
	;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts =[
		'monthly_loan_amounts'=>'array',
		'contract_counts'=>'array',
	];
	// public function category():BelongsTo
	// {
	// 	return $this->belongsTo(LeasingCategory::class,'category_id',) ;
	// }
	// public function getReviewForTable()
	// {
	// 	return $this->category->getTitle().'[' . $this->getLoanNature() . ' / ' . $this->getLoanType(). ' / ' . $this->getTenor(). ' M/ ' . $this->getGracePeriod(). ' M/ ' . $this->getMarginRate(). ' %/ ' . $this->getInstallmentInterval(). ' / ' . $this->getStepRate(). ' %/ ' . $this->getStepInterval() . ' ]';
	// }
	// public function getMonthlyLoanAmountAtYearOrMonthIndex(int $yearOrMonthIndex)
	// {
	// 	return $this->monthly_loan_amounts[$yearOrMonthIndex] ?? 0  ; 
	// }
	
		
}
