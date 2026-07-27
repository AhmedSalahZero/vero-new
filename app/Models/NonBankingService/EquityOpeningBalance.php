<?php
namespace App\Models\NonBankingService;


use App\Helpers\HArr;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $dividend_statement
 * @property numeric $paid_up_capital_amount
 * @property array<array-key, mixed>|null $legal_reserve_extended
 * @property array<array-key, mixed>|null $paid_up_capital_extended
 * @property numeric $legal_reserve
 * @property numeric $retained_earnings
 * @property int $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $statement (DC2Type:json)
 * @property array<array-key, mixed>|null $retained_earning_distribution_amounts
 * @property array<array-key, mixed>|null $retained_earning_distribution_payments
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereDividendStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereLegalReserve($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereLegalReserveExtended($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance wherePaidUpCapitalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance wherePaidUpCapitalExtended($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereRetainedEarningDistributionAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereRetainedEarningDistributionPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereRetainedEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\EquityOpeningBalance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EquityOpeningBalance extends Model
{
	use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
	protected $connection= 'non_banking_service';
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
					$studyMonthsForViews = array_flip($model->study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate());
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
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id','=',$study->id)
		->update(['opening_dividend_payments'=>$statement['monthly']['payment']??[]]);
		return $statement;
	}
	
}
