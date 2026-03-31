<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $counts
 * @property int $start_date
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $operation_date
 * @property int $total_branches
 * @property array<array-key, mixed>|null $rent_payment_amount
 * @property array<array-key, mixed>|null $right_of_use_interest
 * @property array<array-key, mixed>|null $rent_liability_statement
 * @property array<array-key, mixed>|null $right_of_use_statement
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereCounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereOperationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereRentLiabilityStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereRentPaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereRightOfUseInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereRightOfUseStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereTotalBranches($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NewBranchMicrofinanceOpeningProjection extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts =[
		'rent_payment_amount'=>'array',
		'right_of_use_interest'=>'array',
		'rent_liability_statement'=>'array',
		'right_of_use_statement'=>'array',
	];
	public static function boot()
	{
		parent::boot();
		static::saving(function(self $model){
			$study = $model->study ;
			/** @phpstan-ignore-next-line */
			if (is_string($model->start_date)) {
				$startDate = $model->start_date . '-01';
				$startDateAsIndex = $study->convertDateStringToDateIndex($startDate);
				$model->start_date = $startDateAsIndex;
			}
			/** @phpstan-ignore-next-line */
			if (is_string($model->operation_date)) {
				$operationDate = $model->operation_date . '-01';
				$operationDateAsIndex = $study->convertDateStringToDateIndex($operationDate);
				$model->operation_date = $operationDateAsIndex;
			}
		});
	}
	
	public function getCounts():int
	{
		return $this->counts?: 0;
	}
	public function getStartDate():int
	{
		return $this->start_date ?: 0;
	}
	 public function getStartDateYearAndMonth()
    {
        $studyStartDate = $this->getStartDateAsString() ;
        return Carbon::make($studyStartDate)->format('Y-m');
    }
	public function getEndDateAsIndex(int $rightOfUserDuration):int
	{
		return $this->getStartDate() + $rightOfUserDuration-  1 ;
	}
	public function getStartDateAsString():string 
	{
		$dateWithDateIndex = $this->study->getDateIndexWithDate()[$this->getStartDate()];
		return $dateWithDateIndex;
	}
	public function getOperationDate():int
	{
		return $this->operation_date ?: 0;
	}
	public function getOperationDateAsString():string 
	{
		$dateWithDateIndex = $this->study->getDateIndexWithDate()[$this->getOperationDate()];
		return $dateWithDateIndex;
	}
	 public function getOperationDateYearAndMonth()
    {
        $studyStartDate = $this->getOperationDateAsString() ;
       
        return Carbon::make($studyStartDate)->format('Y-m');
    }
	public function getTotalBranches():int
	{
		return $this->total_branches?: 0;
	}
}
