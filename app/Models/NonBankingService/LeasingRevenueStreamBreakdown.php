<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\IsRevenueStream;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $category_id
 * @property string $loan_nature
 * @property string $loan_type
 * @property int $tenor
 * @property int $grace_period
 * @property numeric $margin_rate
 * @property numeric $sensitivity_margin_rate
 * @property numeric $sensitivity_1_margin_rate هيتحكم فيه من الداش بورد
 * @property numeric $sensitivity_2_margin_rate هيتحكم فيه من الداش بورد
 * @property string $installment_interval
 * @property numeric $step_up
 * @property numeric $step_down
 * @property string|null $step_interval
 * @property array<array-key, mixed>|null $loan_amounts
 * @property array<array-key, mixed>|null $monthly_loan_amounts
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\LeasingCategory|null $category
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereGracePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereInstallmentInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereLoanNature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereLoanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereMonthlyLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereSensitivity1MarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereSensitivity2MarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereSensitivityMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereStepDown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereStepInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereStepUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereTenor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeasingRevenueStreamBreakdown whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LeasingRevenueStreamBreakdown extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy , IsRevenueStream;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts =[
		'loan_amounts'=>'array',
		'monthly_loan_amounts'=>'array',
	];
	
	public function category()
	{
		return $this->belongsTo(LeasingCategory::class,'category_id',) ;
	}
	
	public function getReviewForTable()
	{
		if(!$this->category){
			return '---';
		}
	
		return $this->category->getTitle().'[' . $this->getLoanNature() . ' / ' . $this->getLoanType(). ' / ' . $this->getTenor(). ' M/ ' . $this->getGracePeriod(). ' M/ ' . $this->getMarginRate(). ' %/ ' . $this->getInstallmentInterval(). ' / ' . $this->getStepRate(). ' %/ ' . $this->getStepInterval() . ' ]';
	}
	public function getLoanAmountAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->loan_amounts[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getForeignKeyName():string
	{
		return 'leasing_breakdown_id';
	}	
	public function getCategoryColumnName():string 
	{
		return 'category_id';
	}
	public function getRevenueType():string 
	{
		return Study::LEASING;
	}
	public static function getRow(?self $model,Study $study)
	{
		return [
				'id'=>$model ? $model->id : 0,
				'category_id'=>$model ? $model->getCategoryId() : '',
				'loan_nature'=>$model? $model->getLoanNature() : 'fixed-at-end',
				'loan_type'=>$model? $model->getLoanType() : 'normal',
				'tenor'=>$model ? $model->getTenor():12,
				'grace_period'=>$model? $model->getGracePeriod():0,
				'margin_rate'=>$model ? $model->getMarginRate() : 0,
				'installment_interval'=>$model ? $model->getInstallmentInterval():'monthly',
				'step_rate'=>$model ? $model->getStepRate() : 0,
				'step_interval'=>$model? $model->getStepInterval():'annually',
				'company_id'=>$study->company->id,
				'study_id'=>$study->id
		];	
	}
}
