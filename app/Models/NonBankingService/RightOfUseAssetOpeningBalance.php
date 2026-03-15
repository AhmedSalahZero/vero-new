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
 * @property array<array-key, mixed>|null $payload
 * @property array<array-key, mixed> $statement
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\RightOfUseAssetOpeningBalance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RightOfUseAssetOpeningBalance extends Model
{
    use HasCollectionOrPaymentStatement;
    protected $connection= 'non_banking_service';
	protected $table = 'right_of_use_asset_opening_balances';
    protected $guarded = ['id'];

    protected $casts = [
        'payload'=>'array',
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
    public static function getPayloadStatementColumn():string
    {
        return 'payload';
    }
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
            $openingBalance = $model->{self::getOpeningBalanceColumnName()};
            $statementPayload = $model->{self::getPayloadStatementColumn()};
         //   $dateIndexWithDate = $model->study->getDateIndexWithDate();
       //     $study = $model->study;
            $extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
            $dates = range(0, $extendedStudyEndDate);
            // $loansInterestCorridorChanges = [];
            if (!is_null($openingBalance)) {
                $model->statement = self::calculateSettlementStatement($dates, $statementPayload, [], $openingBalance);
                // $beginningBalances = $model->statement['monthly']['beginning_balance']??[];
                // $cbeCorridorChanges = $study->generalAndReserveAssumption  ? $study->generalAndReserveAssumption->getCbeCorridorChangesRates() : [];
                // foreach ($cbeCorridorChanges as $dateAsIndex => $cbeCorridorChange) {
                //     $cbeCorridorChange =$cbeCorridorChange/100;
                //     $loansInterestCorridorChanges[$dateAsIndex] = ($beginningBalances[$dateAsIndex]??0) * $cbeCorridorChange;
                // }
                    
            
            }
                
                
            // $existingInterestExpenses = $model->portfolio_interest_expenses;
            DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')->where('study_id', $model->study->id)->update([
                'right_of_user_amortization'=>json_encode($statementPayload),
                // 'loans_interest_corridor_changes'=>json_encode($loansInterestCorridorChanges)
               ]);
            // DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id', $model->study->id)->update([
            //  'loans_interest_corridor_changes'=>json_encode($loansInterestCorridorChanges),
            // ]);
                        
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
    public function getPayload():array
    {
        return $this->payload?:[] ;
    }
    
    public function getPayloadAtDateIndex(int $dateAsIndex):float
    {
        return $this->getPayload()[$dateAsIndex]??0;
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
