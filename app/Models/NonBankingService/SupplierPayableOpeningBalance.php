<?php
namespace App\Models\NonBankingService;


use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SupplierPayableOpeningBalance extends Model
{
    use HasCollectionOrPaymentStatement;
		protected $connection= 'non_banking_service';
    protected $guarded = ['id'];

    protected $casts = [
        'payload'=>'array',
        'statement'=>'array',
        'portfolio_interest_expenses'=>'array'
        
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
            $dateIndexWithDate = $model->study->getDateIndexWithDate();
					$extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
				$dates = range(0,$extendedStudyEndDate);
				if(!is_null($openingBalance)){
					$model->statement = self::calculateSettlementStatement($dates,$statementPayload, [], $openingBalance, $dateIndexWithDate);
				}
				
				
					$existingInterestExpenses = $model->portfolio_interest_expenses;
					 DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')->where('study_id',$model->study->id)->update([
						 'existing_interests_expense'=>json_encode($existingInterestExpenses)
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
	public function getOdasOutstandingAmount():float
    {
        return $this->odas_outstanding_opening_amount?:0 ;
    }
    public function getPayload():array
    {
        return $this->payload?:[] ;
    }
	
    public function getPayloadAtDateIndex(int $dateAsIndex):float
    {
        return $this->getPayload()[$dateAsIndex]??0;
    }  

	public function getPortfolioInterestExpenses():array
    {
        return $this->portfolio_interest_expenses?:[] ;
    }
	public function getPortfolioInterestExpenseAtDateIndex(int $dateAsIndex):float
    {
        return $this->getPortfolioInterestExpenses()[$dateAsIndex]??0;
    }
    

}
