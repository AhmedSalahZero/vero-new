<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $revenue_stream_type
 * @property int $disbursement_date
 * @property int $securitization_date
 * @property numeric $discount_rate
 * @property numeric $collection_revenue_rate
 * @property numeric $early_settlements_expense_rate
 * @property numeric $expense_amount
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereCollectionRevenueRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereDisbursementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereDiscountRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereEarlySettlementsExpenseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereExpenseAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereRevenueStreamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereSecuritizationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Securitization whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Securitization extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $table ='securitizations';
	protected $guarded = ['id'];
	// protected $casts =[
	// 	'bank_portfolio_loan_schedule_payment_ids'=>'array',	
	// 	'portfolio_loan_schedule_payment_ids'=>'array',	
	// 	'collection_revenue_amounts'=>'array',	
	// ];
	
	public static function boot()
	{
		parent::boot();
		static::saving(function(self $model){
			$study = $model->study ;
			$model->discount_rate = $model->discount_rate ? $model->discount_rate : 0;
			$model->collection_revenue_rate = $model->collection_revenue_rate ? $model->collection_revenue_rate  : 0;
			$model->early_settlements_expense_rate = $model->early_settlements_expense_rate ? $model->early_settlements_expense_rate : 0;
			foreach([
				'disbursement_date',
				'securitization_date'
			] as $dateColumnName){
				/** @phpstan-ignore-next-line */
				if(is_null($model->{$dateColumnName})){
					return false ;
				}
				/** @phpstan-ignore-next-line */
				if(!is_numeric($model->{$dateColumnName})){
					
				$date = $model->{$dateColumnName}.'-01';
				
				$dateAsIndex = $study->convertDateStringToDateIndex($date);
				$model->{$dateColumnName} = $dateAsIndex;
			}
			}
			
			
		});
	}
	
	public function getRevenueStreamType():string 
	{
		return $this->revenue_stream_type ;
	}
	public function getDiscountRate()
	{
		return $this->discount_rate?:0;
	}
	public function getSecuritizationExpenseAmount():float 
	{
		return $this->expense_amount?:0;
	}
	
	public function getSecuritizationDate():int 
	{
		return $this->securitization_date?:0;
	}
    public function getSecuritizationDateFormatted()
    {
        return  app('dateIndexWithDate')[$this->securitization_date] ;
    }
	 public function getSecuritizationDateYearAndMonth()
    {
        $date = $this->getSecuritizationDateFormatted() ;
        return Carbon::make($date)->format('Y-m');
    }
	
	
	public function getDisbursementDate():int 
	{
		return $this->disbursement_date?:0;
	}
    public function getDisbursementDateFormatted()
    {
        return  app('dateIndexWithDate')[$this->disbursement_date];
    }
	 public function getDisbursementDateYearAndMonth()
    {
        $date = $this->getDisbursementDateFormatted() ;
        return Carbon::make($date)->format('Y-m');
    }
	
	public function getCollectionRevenueRate():float 
	{
		return $this->collection_revenue_rate?:0;
	}
	public function getEarlySettlementsExpenseRate():float
	{
		return $this->early_settlements_expense_rate?:0;
	}
}
