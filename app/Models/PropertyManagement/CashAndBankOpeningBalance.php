<?php

namespace App\Models\PropertyManagement;

use App\Helpers\HArr;
use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property numeric $ecl_existing_rate
 * @property numeric $cash_and_bank_amount
 * @property numeric $customer_receivable_amount
 * @property numeric $expected_credit_loss
 * @property array<array-key, mixed>|null $ecl_existing_expenses
 * @property array<array-key, mixed>|null $accumulated_ecl_existing_expenses
 * @property array<array-key, mixed>|null $interests
 * @property string|null $interests_before_adjustment
 * @property array<array-key, mixed>|null $payload
 * @property int $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $statement (DC2Type:json)
 * @property numeric $non_performing_outstanding
 * @property numeric $non_performing_ecl_existing_rate
 * @property numeric $non_performing_expected_credit_loss
 * @property array<array-key, mixed>|null $non_performing_payload
 * @property array<array-key, mixed>|null $non_performing_interests
 * @property string|null $non_performing_interests_before_adjustment
 * @property array<array-key, mixed>|null $non_performing_ecl_existing_expenses
 * @property array<array-key, mixed>|null $non_performing_accumulated_ecl_existing_expenses
 * @property string|null $interest_revenue_corridor_changes
 * @property array<array-key, mixed>|null $non_performing_statement
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereAccumulatedEclExistingExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereCashAndBankAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereCustomerReceivableAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereEclExistingExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereEclExistingRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereExpectedCreditLoss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereInterestRevenueCorridorChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereInterests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereInterestsBeforeAdjustment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingAccumulatedEclExistingExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingEclExistingExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingEclExistingRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingExpectedCreditLoss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingInterests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingInterestsBeforeAdjustment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingOutstanding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereNonPerformingStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashAndBankOpeningBalance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CashAndBankOpeningBalance extends Model
{
	use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
	protected $connection= 'property_management';
	protected $casts = [
		'payload'=>'array',
		'statement'=>'array',
		'interests'=>'array',
		'ecl_existing_expenses'=>'array',
		'accumulated_ecl_existing_expenses'=>'array',
		'non_performing_payload'=>'array',
		'non_performing_interests'=>'array',
		'non_performing_statement'=>'array',
		'non_performing_ecl_existing_expenses'=>'array',
		'non_performing_accumulated_ecl_existing_expenses'=>'array',
	//	'interest_corridor_changes'=>'array',
//		'non_performing_interest_corridor_changes'=>'array',
	
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
				// $nonPerformingOpeningBalance = $model->non_performing_outstanding;
				// $nonPerformingStatementPayload = $model->non_performing_payload;
						$study = $model->study;
				
				 $studyDates = array_keys($study->getStudyDates()) ;
      	  					$sumKeys = $studyDates;
			//	$dateIndexWithDate = $model->study->getDateIndexWithDate();
				$extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
				$dates = range(0,$extendedStudyEndDate);
				if(!is_null($openingBalance)){
					$model->statement = self::calculateSettlementStatement($dates,$statementPayload,[],$openingBalance,false,true);
					$rates = $model->ecl_existing_rate ;
					// $nonPerformingRates = $model->non_performing_ecl_existing_rate ;
					
					$expectedCreditLoss = $model->expected_credit_loss *-1 ;
					// $nonPerformingExpectedCreditLoss = $model->non_performing_expected_credit_loss *-1 ;
					// $rates = [0=>$rates];
					$monthlyRates = [];
					$endBalance = $model->statement['monthly']['end_balance']??[];
				
					$openingBalances = $model->statement['monthly']['beginning_balance']??[];
					// $NonPerformingOpeningBalances = $model->non_performing_statement['monthly']['beginning_balance']??[];
					
					// $totalOpeningBalances = $openingBalances;
					// $cbeCorridorChanges = $study->generalAndReserveAssumption  ? $study->generalAndReserveAssumption->getCbeCorridorChangesRates() : [];
					// $interestCorridorChanges=[];
					// foreach($cbeCorridorChanges as $dateAsIndex => $cbeCorridorChange){
					// 	$cbeCorridorChange =$cbeCorridorChange/100;
					// 	$interestCorridorChanges[$dateAsIndex] = $totalOpeningBalances[$dateAsIndex] * $cbeCorridorChange;
					// }
					
					 foreach($endBalance as $dateAsIndex => $endBalanceAmount){
						$monthlyRates[$dateAsIndex] = $rates;
					 }
					//  $nonPerformingMonthlyRates = [];
					// $nonPerformingEndBalance = $model->non_performing_statement['monthly']['end_balance']??[];
					//  foreach($nonPerformingEndBalance as $dateAsIndex => $endBalanceAmount){
					// 	$nonPerformingMonthlyRates[$dateAsIndex] = $nonPerformingRates;
					//  }
					 	// $eclResult = $model->study->calculateExistingPortfolioEcl($monthlyRates,$endBalance,$expectedCreditLoss);
						// foreach($eclResult as $columnName => $result){
						// 	$model->{$columnName} = $result;
						// }
						// $nonPerformanceEclResult = $model->study->calculateNonPerformingExistingPortfolioEcl($nonPerformingMonthlyRates,$nonPerformingEndBalance,$nonPerformingExpectedCreditLoss);
						// foreach($nonPerformanceEclResult as $columnName => $result){
						// 	$model->{$columnName} = $result;
						// }
				
					//	$model->interests  = $model->interests_before_adjustment;
					//	$model->non_performing_interests  = $model->non_performing_interests_before_adjustment;
							
		  	
						// $totalExistingInterests = HArr::sumAtDates([$model->interests,$model->non_performing_interests],$sumKeys);
					
						//  DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('income_statement_reports')->where('study_id',$model->study->id)->update([
						//  'existing_ecl_expenses'=>json_encode($eclResult['ecl_existing_expenses']),
						//  'non_performing_existing_ecl_expenses'=>json_encode($nonPerformanceEclResult['non_performing_ecl_existing_expenses']),
						//  'existing_interests_revenues'=>json_encode($totalExistingInterests),
						//  'interest_corridor_changes'=>json_encode($interestCorridorChanges),
						// ]);
						
						// DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id',$model->study->id)->update([
						//  'interest_corridor_changes'=>json_encode($interestCorridorChanges),
						// ]);
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
	    public function getCustomerReceivableAmount():float 
    {
        return $this->customer_receivable_amount ;
    }
	public function getEclExistingRate():float 
	{
		return $this->ecl_existing_rate?:0;
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
	
	
	////////////////////
	
	
	public function getNonPerformingOutstanding():float 
    {
        return $this->non_performing_outstanding ;
    }
	public function getNonPerformingEclExistingRate():float 
	{
		return $this->non_performing_ecl_existing_rate?:0;
	}
	  public function getNonPerformingExpectedCreditLossAmount():float 
    {
        return $this->non_performing_expected_credit_loss ;
    }
	public function getNonPerformingPayload():array 
	{
		return $this->non_performing_payload?:[] ;
	}
	public function getNonPerformingInterest():array 
	{
		return $this->non_performing_interests??[] ;
	}
	public function getNonPerformingInterestAtDateIndex(int $dateAsIndex):float 
	{
		return $this->getNonPerformingInterest()[$dateAsIndex]??0;
	}
	
	
	
	public function getNonPerformingPayloadAtDateIndex(int $dateAsIndex):float 
	{
		return $this->getNonPerformingPayload()[$dateAsIndex]??0;
	}
	
	
	// public function getInterestCorridorChanges():array
	// {
	// 	return $this->interest_corridor_changes?:0;
	// }	
	// public function getInterestCorridorChangesAtDateIndex(int $dateAsIndex):float 
	// {
	// 	return $this->getInterestCorridorChanges()[$dateAsIndex]??0;
	// }
	// public function getNonPerformingInterestCorridorChanges():array
	// {
	// 	return $this->non_performing_interest_corridor_changes?:[];
	// }	
	// public function getNonPerformingInterestCorridorChangesAtDateIndex(int $dateAsIndex):float 
	// {
	// 	return $this->getNonPerformingInterestCorridorChanges()[$dateAsIndex]??0;
	// }
	
	
	
	
	
}
