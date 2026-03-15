<?php
namespace App\Models\PropertyManagement;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $name
 * @property numeric $amount
 * @property array<array-key, mixed>|null $payload
 * @property int $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $statement (DC2Type:json)
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\OtherLongTermLiabilitiesOpeningBalance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OtherLongTermLiabilitiesOpeningBalance extends Model
{
    use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
    protected $connection= 'property_management';
    protected $casts = [
        'payload'=>'array',
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
        //    $dateIndexWithDate = $model->study->getDateIndexWithDatE();
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
    public function getPayload():array
    {
        return $this->payload?:[] ;
    }
    public function getPayloadAtDateIndex(int $dateAsIndex):float
    {
        return $this->getPayload()[$dateAsIndex]??0;
    }
    

}
