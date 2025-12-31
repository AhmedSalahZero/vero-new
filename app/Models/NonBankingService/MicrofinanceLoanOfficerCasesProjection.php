<?php
namespace App\Models\NonBankingService;

use App\Helpers\HArr;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class MicrofinanceLoanOfficerCasesProjection extends Model
{
    use HasBasicStoreRequest,CompanyScope , BelongsToStudy;
    protected $connection= 'non_banking_service';
    protected $table= 'microfinance_loan_officers_cases_projects';
    protected $guarded = ['id'];
    public static function boot()
    {
        parent::boot();
        static::saving(function (self $model) {
            $study = $model->study ;
			
            // if (!$study->isMonthlyStudy()) {
                $studyEndDateAsIndex = $study->getStudyEndDateAsIndex();
                $model->existing_cases = repeatLastValueInArrayUntil($model->existing_cases?:[], $studyEndDateAsIndex) ;
                $model->new_cases = repeatLastValueInArrayUntil($model->new_cases?:[], $studyEndDateAsIndex) ;
            // }
            $accumulatedHiring = $model->hiring?:[];
            $totalExistingCasesCounts =[];
            
            foreach ($model->existing_cases?:[] as $dateAsIndex => $value) {
                $existingCount = $model->existing_count;
                $totalExistingCasesCounts[$dateAsIndex] = $value * $existingCount;
            }
          //  $newOfficersCaseCount = [];
            $totalNewOfficersCaseCount = [];
            $isNewBranches = $model->type == 'new-branches' ;
            $branchCounts = $isNewBranches ? $study->newBranchMicrofinanceOpeningProjections->pluck('counts', 'operation_date')->toArray()  : [0=>1];
            $newCases = $model->new_cases?:[];
            foreach ($branchCounts as $branchDateAsIndex => $branchCount) {
                foreach ($accumulatedHiring as $dateAsIndex => $hiring) {
                    foreach ($newCases as $index => $currentNewCount) {
                        $index = $index + $dateAsIndex + $branchDateAsIndex;
                        $currentValue = $currentNewCount * $hiring * $branchCount;
				
                        $totalNewOfficersCaseCount[$index] = isset($totalNewOfficersCaseCount[$index]) ? $totalNewOfficersCaseCount[$index] +$currentValue : $currentValue ;
                    }
                }
            }
			
			
	
            $model->total_existing_officers_cases_count = $totalExistingCasesCounts;
            $model->total_new_officers_cases_count = $totalNewOfficersCaseCount;
			
                
        
        });
          
    }
    protected $casts = [
        'total_existing_officers_cases_count'=>'array',
        'total_new_officers_cases_count'=>'array',
        'existing_cases'=>'array',
        'hiring'=>'array',
        'new_cases'=>'array',
        'flat_rates'=>'array',
        'decrease_rates'=>'array',
        ];
   
    public function getExistingLoanCasesAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
        return $this->existing_cases[$yearOrDateIndex]??0;
    }
    public function getNewLoanCasesAtYearOrMonthIndex(int $yearOrDateIndex , $isSenior = null):float
    {
		if($this->type =='by-branch'){
			if($isSenior){
				return $this->study->product_mix_senior_loan_officers[$yearOrDateIndex];
			}
			return $this->study->product_mix_loan_officers[$yearOrDateIndex];
		}
        return $this->new_cases[$yearOrDateIndex]??0;
    }	public function getHiringAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
        return $this->hiring[$yearOrDateIndex]??0;
    }
    public function getTotalCasesCountAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
        return $this->total_existing_officers_cases_count[$yearOrDateIndex]??0;
    }
    public function getExistingCount():int
    {
        return $this->existing_count?:0;
    }
}
