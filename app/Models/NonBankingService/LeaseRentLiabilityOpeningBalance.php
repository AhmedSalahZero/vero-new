<?php
namespace App\Models\NonBankingService;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $study_id
 * @property string|null $name
 * @property numeric $amount
 * @property array<array-key, mixed>|null $rent_payment
 * @property array<array-key, mixed>|null $rent_interest
 * @property array<array-key, mixed> $statement
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereRentInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereRentPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LeaseRentLiabilityOpeningBalance whereUpdatedAt($value)
 * @mixin \Eloquent
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
