<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  MicrofinanceByBranchProductMix extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts = [
		'flat_rates'=>'array',
		'decrease_rates'=>'array',
		'senior_loan_officers'=>'array',
		'loan_officers'=>'array',
		'increase_rates'=>'array',
		];
		
		public function getTenor():int
    {
        return $this->tenor ;
    }
    public function getAvgAmount():float
    {
        return $this->avg_amount;
    }
	public function getEarlyPaymentInstallmentCounts():int
    {
        return $this->early_payment_installment_counts?:0;
    }
    public function getFundedBy():string
    {
        return $this->funded_by ;
    }
	  public function getFundedByFormatted():string
    {
        $fundedBy = $this->getFundedBy();
        foreach (getMicrofinanceFundingBySelector() as $arr) {
            if ($arr['value'] == $fundedBy) {
                return $arr['title'];
            }
        }
		return __('N/A');
    }
	 public function getFlatRateAtYearOrMonthIndex(int $yearOrDateIndex):float
    {
        return $this->flat_rates[$yearOrDateIndex]??0;
    }
    public function getIncreaseRateAtYearIndex($yearIndex)
    {
        return $this->increase_rates[$yearIndex] ?? 0;
    }
}
