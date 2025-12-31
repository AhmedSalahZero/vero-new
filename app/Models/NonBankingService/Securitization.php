<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class  Securitization extends Model
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
			foreach([
				'disbursement_date',
				'securitization_date'
			] as $dateColumnName){
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
	
	public function getSecuritizationDate():float 
	{
		return $this->securitization_date?:0;
	}
    public function getSecuritizationDateFormatted()
    {
        return !is_null($this->securitization_date) ? app('dateIndexWithDate')[$this->securitization_date] : null;
    }
	 public function getSecuritizationDateYearAndMonth()
    {
        $date = $this->getSecuritizationDateFormatted() ;
        if (is_null($date)) {
            return now()->format('Y-m');
        }
        return Carbon::make($date)->format('Y-m');
    }
	
	
	public function getDisbursementDate():float 
	{
		return $this->disbursement_date?:0;
	}
    public function getDisbursementDateFormatted()
    {
        return !is_null($this->disbursement_date)  ? app('dateIndexWithDate')[$this->disbursement_date] : null;
    }
	 public function getDisbursementDateYearAndMonth()
    {
        $date = $this->getDisbursementDateFormatted() ;
        if (is_null($date)) {
            return now()->format('Y-m');
        }
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
