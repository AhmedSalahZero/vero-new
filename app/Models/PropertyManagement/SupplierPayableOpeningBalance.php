<?php
namespace App\Models\PropertyManagement;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property numeric $amount
 * @property array<array-key, mixed>|null $payload
 * @property int $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $statement (DC2Type:json)
 * @property numeric $odas_outstanding_opening_amount
 * @property array<array-key, mixed>|null $portfolio_interest_expenses
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance whereOdasOutstandingOpeningAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance wherePortfolioInterestExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\SupplierPayableOpeningBalance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SupplierPayableOpeningBalance extends Model
{
    use HasCollectionOrPaymentStatement;
    protected $connection= 'property_management';
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
        //    $dateIndexWithDate = $model->study->getDateIndexWithDate();
        //    $study = $model->study;
            $extendedStudyEndDate = $model->study->convertDateStringToDateIndex($model->study->getEndDate()) ;
            $dates = range(0, $extendedStudyEndDate);
            // $loansInterestCorridorChanges = [];
            if (!is_null($openingBalance)) {
                $model->statement = self::calculateSettlementStatement($dates, $statementPayload, [], $openingBalance);
                // $beginningBalances = $model->statement['monthly']['beginning_balance']??[];
    
                // $cbeCorridorChanges = $study->generalAndReserveAssumption  ? $study->generalAndReserveAssumption->getCbeCorridorChangesRates() : [];
                // foreach ($cbeCorridorChanges as $dateAsIndex => $cbeCorridorChange) {
                //     $cbeCorridorChange =$cbeCorridorChange/100;
                //     $loansInterestCorridorChanges[$dateAsIndex] = ($beginningBalances[$dateAsIndex]??0) * $cbeCorridorChange;
                // }
                    
            
            }
                
                
            // $existingInterestExpenses = $model->portfolio_interest_expenses;
            // DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('income_statement_reports')->where('study_id', $model->study->id)->update([
                // 'existing_interests_expense'=>json_encode($existingInterestExpenses),
                // 'loans_interest_corridor_changes'=>json_encode($loansInterestCorridorChanges)
            //    ]);
            // DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id', $model->study->id)->update([
            //  'loans_interest_corridor_changes'=>json_encode($loansInterestCorridorChanges),
            // ]);
                        
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
