<?php
namespace App\Models\NonBankingService;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $study_id
 * @property string|null $name
 * @property numeric $amount
 * @property array<array-key, mixed> $statement
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\LongTermInvestmentsOpeningBalance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LongTermInvestmentsOpeningBalance extends Model
{
    use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
    protected $connection= 'non_banking_service';
    protected $casts = [
   //    'payload'=>'array',
        'statement'=>'array'
    ];
	public function getName():?string 
	{
		return $this->name ;
	}
    public static function getOpeningBalanceColumnName():string
    {
        return 'amount';
    }
	public function setAmountAttribute($value)
	{
		$this->attributes['amount'] = number_unformat($value);
	}
    // public static function getPayloadStatementColumn():string
    // {
    //     return 'payload';
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
            $openingBalance = $model->{self::getOpeningBalanceColumnName()};
            $statementPayload = [];
      //      $dateIndexWithDate = $model->study->getDateIndexWithDatE();
            $extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
            $dates = range(0, $extendedStudyEndDate);
            if (!is_null($openingBalance)) {
                $model->statement = self::calculateSettlementStatement($dates, $statementPayload, [], $openingBalance);
            }
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
    // public function getPayload():array
    // {
    //     return $this->payload?:[] ;
    // }
    // public function getPayloadAtDateIndex(int $dateAsIndex):float
    // {
    //     return $this->getPayload()[$dateAsIndex]??0;
    // }
    

}
