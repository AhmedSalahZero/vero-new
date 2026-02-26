<?php
namespace App\Models\Trading;


use App\Helpers\HArr;
use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class EquityOpeningBalance extends Model
{
	use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
	protected $connection=TRADING_CONNECTION_NAME;
protected $casts = [
		'payload'=>'array',
		'statement'=>'array',
		'paid_up_capital_extended'=>'array',
		'legal_reserve_extended'=>'array',
		'retained_earning_distribution_amounts'=>'array',
		'retained_earning_distribution_payments'=>'array',
		'dividend_statement'=>'array'
	];
	public static function booted()
	{
			parent::boot();
			static::saving(function(self $model){
				$study = $model->study;
					$studyMonthsForViews = array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate());
     			   	$sumKeys = array_keys($studyMonthsForViews);
					$model->paid_up_capital_extended = HArr::repeatThrough($model->paid_up_capital_amount?:0,$sumKeys);
					$model->legal_reserve_extended = HArr::repeatThrough($model->legal_reserve?:0,$sumKeys);
					$model->dividend_statement = self::calculateDividendStatement($model->study,$model->retained_earning_distribution_amounts,$model->retained_earning_distribution_payments);
			});
	}
	
	
    public function study():BelongsTo
    {
        return $this->belongsTo(Study::class, 'study_id', 'id');
    }
	
    public function getPaidUpCapitalAmount():float 
    {
        return $this->paid_up_capital_amount ;
    } 
	public function getExtendedPaidUpCapitalAmount():array 
    {
        return $this->paid_up_capital_extended?:[] ;
    } 
	public function getLegalReserveAmount():float 
    {
        return $this->legal_reserve ;
    }
	public function getExtendedLegalReserveAmount():array 
    {
        return $this->legal_reserve_extended?:[] ;
    } 
	public function getRetainedEarningAmount():float 
    {
        return $this->retained_earnings ;
    }
	public function getTotalShareholdersEquity():float
	{
		return $this->getRetainedEarningAmount() + $this->getLegalReserveAmount() + $this->getPaidUpCapitalAmount();
	}
	
	public function getRetainedEarningDistributionAmounts():array 
	{
		return $this->retained_earning_distribution_amounts??[] ;
	}
	public function getRetainedEarningDistributionAmountsAtYearOrMonthIndex(int $dateAsIndex):float 
	{
		return $this->getRetainedEarningDistributionAmounts()[$dateAsIndex]??0;
	}
	
	public function getRetainedEarningDistributionPayments():array 
	{
		return $this->retained_earning_distribution_payments??[] ;
	}
	public function getRetainedEarningDistributionPaymentsAtYearOrMonthIndex(int $dateAsIndex):float 
	{
		return $this->getRetainedEarningDistributionPayments()[$dateAsIndex]??0;
	}
	public static function calculateDividendStatement(Study $study,array $dividendAmounts, array $dividendPayments):array 
	{
		 $dateIndexWithDate = $study->getDateIndexWithDate();
		$beginningBalance = 0 ;
		$statement=self::calculateStatement($dividendAmounts,[],$dividendPayments,[],$dateIndexWithDate,$beginningBalance);
		DB::connection(TRADING_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id','=',$study->id)
		->update(['opening_dividend_payments'=>$statement['monthly']['payment']??[]]);
		return $statement;
	}
	
}
