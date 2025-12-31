<?php
namespace App\Models\NonBankingService;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LongTermLoanOpeningBalance extends Model
{
    use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
    protected $connection= 'non_banking_service';
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
            $openingBalance = $model->{self::getOpeningBalanceColumnName()};
            $statementPayload = $model->{self::getPayloadStatementColumn()};
            $dateIndexWithDate = $model->study->getDateIndexWithDate();
            if (!is_null($openingBalance)) {
                $extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
                $dates = range(0, $extendedStudyEndDate);
                $model->statement = self::calculateSettlementStatement($dates, $statementPayload, [], $openingBalance, $dateIndexWithDate);
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
