<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class  NewBranchMicrofinanceOpeningProjection extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts =[
	];
	public static function boot()
	{
		parent::boot();
		static::saving(function(self $model){
			$study = $model->study ;
			if(!is_numeric($model->start_date)){
				$startDate = $model->start_date.'-01';
				$startDateAsIndex = $study->convertDateStringToDateIndex($startDate);
				$model->start_date = $startDateAsIndex;
			}
			if(!is_numeric($model->operation_date)){
				$operationDate = $model->operation_date.'-01';
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
        if (is_null($studyStartDate)) {
            return now()->format('Y-m');
        }
        return Carbon::make($studyStartDate)->format('Y-m');
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
        if (is_null($studyStartDate)) {
            return now()->format('Y-m');
        }
        return Carbon::make($studyStartDate)->format('Y-m');
    }
	public function getTotalBranches():int
	{
		return $this->total_branches?: 0;
	}
}
