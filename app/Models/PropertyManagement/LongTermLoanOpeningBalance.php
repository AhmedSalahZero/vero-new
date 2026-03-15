<?php
namespace App\Models\PropertyManagement;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $name
 * @property numeric $amount
 * @property numeric $interest_rate
 * @property array<array-key, mixed>|null $interests
 * @property array<array-key, mixed>|null $installments
 * @property int $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $statement (DC2Type:json)
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereInstallments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereInterests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\LongTermLoanOpeningBalance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LongTermLoanOpeningBalance extends Model
{
    use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
    protected $connection= 'property_management';
    protected $casts = [
        'interests'=>'array',
        'installments'=>'array',
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
    public static function getPayloadStatementColumn():string
    {
        return 'installments';
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
          //  $dateIndexWithDate = $model->study->getDateIndexWithDate();
            if (!is_null($openingBalance)) {
                $extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
                $dates = range(0, $extendedStudyEndDate);
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
	public function getInterestRate():float
    {
        return $this->interest_rate?:0 ;
    }
    public function getInterest():array
    {
        return $this->interests??[] ;
    }
    public function getInterestAtDateIndex(int $dateAsIndex):float
    {
        return $this->getInterest()[$dateAsIndex]??0;
    }
    public function getInstallment():array
    {
        return $this->installments??[] ;
    }
    public function getInstallmentAtDateIndex(int $dateAsIndex):float
    {
        return $this->getInstallment()[$dateAsIndex]??0;
    }

}
