<?php

namespace App\Models\NonBankingService;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class CashAndBankOpeningBalance extends Model
{
	use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
	protected $connection= 'non_banking_service';
	protected $casts = [
		'payload'=>'array',
		'statement'=>'array',
		'interests'=>'array',
		'ecl_existing_expenses'=>'array',
		'accumulated_ecl_existing_expenses'=>'array',
	];
	public static function getOpeningBalanceColumnName():string
	{
		return 'customer_receivable_amount';
	}
	public static function getPayloadStatementColumn():string 
	{
		return 'payload';
	}
	public static function booted()
	{
			parent::boot();
			static::saving(function(self $model){
				$openingBalance = $model->{self::getOpeningBalanceColumnName()};
				$statementPayload = $model->{self::getPayloadStatementColumn()};
				$dateIndexWithDate = $model->study->getDateIndexWithDate();
				$extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
				$dates = range(0,$extendedStudyEndDate);
				if(!is_null($openingBalance)){
					$model->statement = self::calculateSettlementStatement($dates,$statementPayload,[],$openingBalance,$dateIndexWithDate,false,true);
					$rates = $model->ecl_existing_rate ;
					
					$expectedCreditLoss = $model->expected_credit_loss *-1 ;
					// $rates = [0=>$rates];
					$monthlyRates = [];
					$endBalance = $model->statement['monthly']['end_balance']??[];
					 foreach($endBalance as $dateAsIndex => $endBalanceAmount){
						$monthlyRates[$dateAsIndex] = $rates;
					 }
					 $eclResult = $model->study->calculateExistingPortfolioEcl($monthlyRates,$endBalance,$expectedCreditLoss);
						foreach($eclResult as $columnName => $result){
							$model->{$columnName} = $result;
						}
						$interests = $model->interests;
						 DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')->where('study_id',$model->study->id)->update([
						 'existing_ecl_expenses'=>json_encode($eclResult['ecl_existing_expenses']),
						 'existing_interests_revenues'=>json_encode($interests)
						]);
						
				}
			});
	}
    public function study():BelongsTo
    {
        return $this->belongsTo(Study::class, 'study_id', 'id');
    }
	
    public function getCashAndBankAmount():float 
    {
        return $this->cash_and_bank_amount ;
    }
	public function getEclExistingRate():float 
	{
		return $this->ecl_existing_rate?:0;
	}
	    public function getCustomerReceivableAmount():float 
    {
        return $this->customer_receivable_amount ;
    }
	  public function getExpectedCreditLossAmount():float 
    {
        return $this->expected_credit_loss ;
    }
	public function getPayload():array 
	{
		return $this->payload?:[] ;
	}
	public function getInterest():array 
	{
		return $this->interests??[] ;
	}
	
	public function getInterestAtDateIndex(int $dateAsIndex):float 
	{
		return $this->getInterest()[$dateAsIndex]??0;
	}
	public function getPayloadAtDateIndex(int $dateAsIndex):float 
	{
		return $this->getPayload()[$dateAsIndex]??0;
	}
	
	
}
