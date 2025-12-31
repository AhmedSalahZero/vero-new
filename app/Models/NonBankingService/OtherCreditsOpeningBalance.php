<?php
namespace App\Models\NonBankingService;


use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtherCreditsOpeningBalance extends Model
{
	  use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
	protected $connection= 'non_banking_service';
	protected $casts = [
		'payload'=>'array',
		'statement'=>'array'
		
	];
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
            $openingBalance = $model->{self::getOpeningBalanceColumnName()};
            $statementPayload = $model->{self::getPayloadStatementColumn()};
            $dateIndexWithDate = $model->study->getDateIndexWithDatE();
				$extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
				$dates = range(0,$extendedStudyEndDate);
				if(!is_null($openingBalance)){
					$model->statement = self::calculateSettlementStatement($dates,$statementPayload, [], $openingBalance, $dateIndexWithDate);
				}
        });
    }
	
    public function study():BelongsTo
    {
        return $this->belongsTo(Study::class, 'study_id', 'id');
    }
	
	public function getName()
	{
		return $this->name;
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
