<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $securitization_id
 * @property numeric $portfolio_disbursement_amount
 * @property numeric $portfolio_schedule_payment_sum
 * @property numeric $net_present_value
 * @property numeric $bank_portfolio_end_balance_sum
 * @property numeric $securitization_profit_or_loss
 * @property array<array-key, mixed>|null $collection_revenue_amounts
 * @property numeric $early_settlements_expense_amount
 * @property numeric $securitization_expense_amount
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property numeric $portfolio_principle_amount_sum
 * @property-read \App\Models\NonBankingService\Securitization|null $securitization
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereBankPortfolioEndBalanceSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereCollectionRevenueAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereEarlySettlementsExpenseAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereNetPresentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule wherePortfolioDisbursementAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule wherePortfolioPrincipleAmountSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule wherePortfolioSchedulePaymentSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereSecuritizationExpenseAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereSecuritizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereSecuritizationProfitOrLoss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\SecuritizationLoanSchedule whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SecuritizationLoanSchedule extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $table ='securitization_loan_schedules';
	protected $guarded = ['id'];
	protected $casts =[
		// 'bank_portfolio_loan_schedule_payment_ids'=>'array',	
		// 'portfolio_loan_schedule_payment_ids'=>'array',	
		'collection_revenue_amounts'=>'array',	
	];
	public function securitization()
	{
		return $this->belongsTo(Securitization::class,'securitization_id','id');
	}
	
	
}
