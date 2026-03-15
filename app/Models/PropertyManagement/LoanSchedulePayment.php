<?php
namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
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
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereAccuredInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereBeginning($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereEndBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereInterestAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereInterestCorridorChangeStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereInterestPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereLoanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereMonthAsIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment wherePortfolioLoanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment wherePrincipleAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereRevenueStreamCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereRevenueStreamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereRevenueStreamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereSchedulePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereSecuritizationDateIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereTotals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LoanSchedulePayment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LoanSchedulePayment extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'property_management';
	protected $guarded = ['id'];
	protected $casts =[
	
	];
	
	
}
