<?php
namespace App\Models\NonBankingService;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @mixin IdeHelperLeaseRentLiabilityOpeningBalance
 */
class LeaseRentLiabilityOpeningBalance extends Model
{
    use HasCollectionOrPaymentStatement;
    protected $connection= 'non_banking_service';
	protected $table = 'lease_rent_liability_opening_opening_balances';
    protected $guarded = ['id'];

    protected $casts = [
        'rent_payment'=>'array',
        'rent_interest'=>'array',
        'statement'=>'array',
    ];
	public function getName()
	{
		return $this->name;
	}
    public static function getOpeningBalanceColumnName():string
    {
        return 'amount';
    }
    // public static function getPayloadStatementColumn():string
    // {
    //     return 'rent_interest';
    // }
	// public static function getInterestStatementColumn():string
    // {
    //     return '';
    // }
    public static function booted()
    {
        parent::boot();
        static::saving(function (self $model) {
			
			if(is_null($model->name) && $model->amount == 0){
				if($model->exists){
					$model->delete();
				}
					return false;
			}
			
			
            $openingBalance = $model->amount;
            $statementPayload = $model->rent_payment;
			$additions = $model->rent_interest;
         //   $dateIndexWithDate = $model->study->getDateIndexWithDate();
            // $study = $model->study;
            $extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
            $dates = range(0, $extendedStudyEndDate);
            if (!is_null($openingBalance)) {
                $model->statement = self::calculateSettlementStatement($dates, $statementPayload, $additions, $openingBalance);
              
                    
            
            }
                
                
            DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')->where('study_id', $model->study->id)->update([
                'rent_interest'=>json_encode($additions),
				// 'rent_payment'=>$statementPayload
               ]);
            DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id', $model->study->id)->update([
             'rent_payment'=>json_encode($statementPayload),
            ]);
                        
        });
    }
    public function study():BelongsTo
    {
        return $this->belongsTo(Study::class, 'study_id', 'id');
    }
    
    public function getAmount():float
    {
        return $this->amount ;
    }
    // public function getOdasOutstandingAmount():float
    // {
    //     return $this->odas_outstanding_opening_amount?:0 ;
    // }
    public function getRentPayment():array
    {
        return $this->rent_payment?:[] ;
    }
    
    public function getRentPaymentAtDateIndex(int $dateAsIndex):float
    {
        return $this->getRentPayment()[$dateAsIndex]??0;
    }
	public function getRentInterest():array
    {
        return $this->rent_interest?:[] ;
    }
    
    public function getRentInterestAtDateIndex(int $dateAsIndex):float
    {
        return $this->getRentInterest()[$dateAsIndex]??0;
    }


    // public function getPortfolioInterestExpenses():array
    // {
    //     return $this->portfolio_interest_expenses?:[] ;
    // }
    // public function getPortfolioInterestExpenseAtDateIndex(int $dateAsIndex):float
    // {
    //     return $this->getPortfolioInterestExpenses()[$dateAsIndex]??0;
    // }
    

}
