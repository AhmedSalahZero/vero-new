<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $revenue_stream_type leasing or factoring or ...
 * @property string|null $portfolio_loan_type bank_portfolio or portfolio
 * @property int|null $revenue_stream_id LeasingRevenueStreamBreakdown
 * @property int|null $revenue_stream_category_id LeasingCategory
 * @property int $month_as_index
 * @property string|null $loan_type
 * @property string|null $totals
 * @property string $beginning
 * @property string $interestAmount
 * @property string|null $interestCorridorChangeStatement
 * @property string $schedulePayment
 * @property string|null $accured_interest
 * @property string|null $InterestPayment
 * @property string $principleAmount
 * @property string $endBalance
 * @property int|null $securitization_date_index
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $study_id
 * @property int $company_id
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereAccuredInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereBeginning($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereInterestAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereInterestCorridorChangeStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereInterestPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereLoanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereMonthAsIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment wherePortfolioLoanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment wherePrincipleAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereRevenueStreamCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereRevenueStreamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereRevenueStreamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereSchedulePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereSecuritizationDateIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereTotals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LoanSchedulePayment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LoanSchedulePayment extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts =[
	
	];
	
	
}
