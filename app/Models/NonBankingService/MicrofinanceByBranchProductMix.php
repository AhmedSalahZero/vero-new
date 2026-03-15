<?php
namespace App\Models\NonBankingService;

use App\Helpers\HNonBanking;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $tenor
 * @property numeric $avg_amount
 * @property int $early_payment_installment_counts
 * @property string $funded_by
 * @property int $microfinance_product_id
 * @property array<array-key, mixed>|null $flat_rates
 * @property array<array-key, mixed>|null $decrease_rates
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $id
 * @property array<array-key, mixed>|null $increase_rates
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereAvgAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereDecreaseRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereEarlyPaymentInstallmentCounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereFlatRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereFundedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereIncreaseRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereMicrofinanceProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereTenor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceByBranchProductMix whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MicrofinanceByBranchProductMix extends Model
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
        foreach (HNonBanking::getMicrofinanceFundingBySelector() as $arr) {
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
